<?php

$root = dirname(__DIR__) . '/resources/views/r_customer';
$files = array_merge(
    glob($root . '/*.blade.php') ?: [],
    glob($root . '/partials/*.blade.php') ?: []
);

$fixes = [
    'style="class="' => 'BROKEN_CLASS_MARKER',
    'BROKEN_CLASS_MARKER' => '', // handled below
];

foreach ($files as $file) {
    $content = file_get_contents($file);
    $original = $content;

    // style="class="foo" style="..." -> class="foo" style="..."
    $content = preg_replace(
        '/\bstyle="class="([^"]+)"\s+style="/',
        'class="$1" style="',
        $content
    );

    // <tag class="x" style="class="y" style=" -> merge duplicate (faq-item)
    $content = preg_replace(
        '/class="faq-item"\s+style="class="faq-item cust-glass"\s+style="/',
        'class="faq-item cust-glass" style="',
        $content
    );

    // press-btn with broken class in style
    $content = preg_replace(
        '/class="([^"]*press-btn[^"]*)"\s+style="class="([^"]+)"\s+style="/',
        'class="$1 $2" style="',
        $content
    );

    // duplicate faq-chevron class
    $content = str_replace('class="faq-chevron" class="faq-chevron"', 'class="faq-chevron"', $content);

    if ($content !== $original) {
        file_put_contents($file, $content);
        echo str_replace(dirname(__DIR__) . DIRECTORY_SEPARATOR, '', $file) . PHP_EOL;
    }
}
