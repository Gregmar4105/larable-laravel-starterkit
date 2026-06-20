<?php

namespace App\Http\Controllers\Larable;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\File;

/**
 * Obsidian Graph Controller
 *
 * Scans the docs/ folder for markdown files, parses [[wiki-links]]
 * to build a graph of interconnected documentation, and returns
 * nodes/edges for D3.js force-directed visualization.
 */
class GraphController extends Controller
{
    /**
     * Get the documentation graph data.
     */
    public function index(): JsonResponse
    {
        $docsPath = base_path('docs');

        if (! File::isDirectory($docsPath)) {
            return response()->json(['nodes' => [], 'edges' => []]);
        }

        $files = File::allFiles($docsPath);
        $nodes = [];
        $edges = [];
        $fileMap = []; // basename → relative path

        // First pass: collect all markdown files
        foreach ($files as $file) {
            if ($file->getExtension() !== 'md') {
                continue;
            }

            $relativePath = str_replace('\\', '/', $file->getRelativePathname());
            $basename = pathinfo($file->getFilename(), PATHINFO_FILENAME);

            $fileMap[$basename] = $relativePath;

            $content = File::get($file->getPathname());
            $links = $this->extractWikiLinks($content);
            $title = $this->extractTitle($content) ?: $basename;

            $nodes[] = [
                'id' => $relativePath,
                'label' => $title,
                'basename' => $basename,
                'path' => $relativePath,
                'links' => $links,
                'size' => $file->getSize(),
                'lines' => substr_count($content, "\n") + 1,
            ];
        }

        // Second pass: resolve links to edges
        foreach ($nodes as &$node) {
            $connectionCount = 0;
            foreach ($node['links'] as $link) {
                // Try to resolve the link to a file
                $targetPath = $fileMap[$link] ?? null;

                if ($targetPath && $targetPath !== $node['id']) {
                    $edges[] = [
                        'source' => $node['id'],
                        'target' => $targetPath,
                        'label' => $link,
                    ];
                    $connectionCount++;
                }
            }
            $node['connections'] = $connectionCount;
            unset($node['links']); // Remove raw links from output
        }

        // Count incoming connections
        $incomingCount = [];
        foreach ($edges as $edge) {
            $incomingCount[$edge['target']] = ($incomingCount[$edge['target']] ?? 0) + 1;
        }

        foreach ($nodes as &$node) {
            $node['connections'] += ($incomingCount[$node['id']] ?? 0);
        }

        return response()->json([
            'nodes' => array_values($nodes),
            'edges' => $edges,
        ]);
    }

    /**
     * Get the content of a specific documentation file.
     */
    public function fileContent(string $path): JsonResponse
    {
        $fullPath = base_path('docs/'.$path);

        if (! File::exists($fullPath) || ! str_ends_with($fullPath, '.md')) {
            return response()->json(['error' => 'File not found'], 404);
        }

        $content = File::get($fullPath);
        $title = $this->extractTitle($content) ?: pathinfo($path, PATHINFO_FILENAME);

        return response()->json([
            'path' => $path,
            'title' => $title,
            'content' => $content,
        ]);
    }

    /**
     * Extract [[wiki-links]] from markdown content.
     *
     * @return string[]
     */
    protected function extractWikiLinks(string $content): array
    {
        preg_match_all('/\[\[([^\]]+)\]\]/', $content, $matches);

        return array_unique($matches[1] ?? []);
    }

    /**
     * Extract the first H1 title from markdown content.
     */
    protected function extractTitle(string $content): ?string
    {
        if (preg_match('/^#\s+(.+)$/m', $content, $match)) {
            return trim($match[1]);
        }

        return null;
    }
}
