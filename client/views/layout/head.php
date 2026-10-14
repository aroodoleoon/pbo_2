<?php
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?= htmlspecialchars($pageTitle ?? 'POS Terminal') ?> — POS Academic</title>
    
    <!-- Google Fonts & Material Symbols Outlined -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
    
    <!-- Tailwind CSS with Stitch Theme Palette -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "surface-container": "#eaedff",
                        "inverse-on-surface": "#eef0ff",
                        "on-secondary": "#ffffff",
                        "on-background": "#131b2e",
                        "inverse-primary": "#c3c0ff",
                        "on-primary-fixed-variant": "#3323cc",
                        "on-tertiary-container": "#ffd4a4",
                        "on-surface": "#131b2e",
                        "surface-container-highest": "#dae2fd",
                        "primary-container": "#4f46e5",
                        "error": "#ba1a1a",
                        "on-primary-fixed": "#0f0069",
                        "surface": "#faf8ff",
                        "surface-container-high": "#e2e7ff",
                        "tertiary-container": "#885500",
                        "outline": "#777587",
                        "secondary-container": "#6cf8bb",
                        "on-tertiary-fixed": "#2a1700",
                        "secondary-fixed": "#6ffbbe",
                        "on-primary": "#ffffff",
                        "on-error-container": "#93000a",
                        "on-secondary-container": "#00714d",
                        "surface-container-lowest": "#ffffff",
                        "tertiary-fixed-dim": "#ffb95f",
                        "surface-dim": "#d2d9f4",
                        "secondary": "#006c49",
                        "on-secondary-fixed-variant": "#005236",
                        "surface-bright": "#faf8ff",
                        "on-tertiary-fixed-variant": "#653e00",
                        "outline-variant": "#c7c4d8",
                        "secondary-fixed-dim": "#4edea3",
                        "on-secondary-fixed": "#002113",
                        "on-error": "#ffffff",
                        "tertiary": "#684000",
                        "on-tertiary": "#ffffff",
                        "error-container": "#ffdad6",
                        "background": "#faf8ff",
                        "inverse-surface": "#283044",
                        "primary-fixed-dim": "#c3c0ff",
                        "on-primary-container": "#dad7ff",
                        "primary-fixed": "#e2dfff",
                        "surface-container-low": "#f2f3ff",
                        "surface-variant": "#dae2fd",
                        "on-surface-variant": "#464555",
                        "surface-tint": "#4d44e3",
                        "primary": "#3525cd",
                        "tertiary-fixed": "#ffddb8"
                    },
                    borderRadius: {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "2xl": "1rem",
                        "full": "9999px"
                    },
                    spacing: {
                        "gutter-lg": "1.5rem",
                        "space-xs": "0.25rem",
                        "space-lg": "1rem",
                        "space-md": "0.75rem",
                        "space-sm": "0.5rem",
                        "gutter": "1rem",
                        "margin": "1rem",
                        "space-xl": "1.5rem",
                        "margin-lg": "1.5rem"
                    },
                    fontFamily: {
                        "body-md": ["Plus Jakarta Sans", "sans-serif"],
                        "headline-xl": ["Plus Jakarta Sans", "sans-serif"],
                        "numeric-total": ["Plus Jakarta Sans", "sans-serif"],
                        "body-lg": ["Plus Jakarta Sans", "sans-serif"],
                        "label-md": ["Plus Jakarta Sans", "sans-serif"],
                        "numeric-md": ["Plus Jakarta Sans", "sans-serif"],
                        "headline-md": ["Plus Jakarta Sans", "sans-serif"],
                        "headline-lg": ["Plus Jakarta Sans", "sans-serif"],
                        "label-sm": ["Plus Jakarta Sans", "sans-serif"],
                        "body-sm": ["Plus Jakarta Sans", "sans-serif"]
                    }
                }
            }
        };
    </script>
    <style>
        @layer base {
            html, body { margin: 0; padding: 0; }
            body { overscroll-behavior: none; }
        }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f2f3ff; }
        ::-webkit-scrollbar-thumb { background: #c7c4d8; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #777587; }
    </style>
</head>
<body class="bg-surface font-body-md text-on-surface antialiased flex flex-col min-h-screen">
