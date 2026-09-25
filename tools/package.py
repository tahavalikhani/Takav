"""Export the real WordPress home/product pages and package a portable preview.
Usage: python3 tools/package.py http://127.0.0.1:8898 /path/to/outputs
"""
from pathlib import Path
from html import escape, unescape
import re
import shutil
import sys
import urllib.request
import zipfile

root = Path(__file__).resolve().parents[1]
base_url = sys.argv[1].rstrip('/')
output = Path(sys.argv[2]).resolve()
output.mkdir(parents=True, exist_ok=True)
preview = output / 'takav-preview'
preview.mkdir(exist_ok=True)
routes = {'index.html': '/', 'hoodie.html': '/?takav_product=hoodie', 'pants.html': '/?takav_product=pants', 'collection-one.html': '/?takav_view=collection-one', 'cart.html': '/?takav_view=cart'}
peyda = all((root / f'theme/takav/assets/fonts/Peyda-{weight}.ttf').exists() for weight in ('Light', 'Regular', 'Medium'))
font = 'Peyda-Light.ttf' if peyda else 'Vazirmatn.woff2'
font_type = 'ttf' if peyda else 'woff2'
for filename, route in routes.items():
    html = urllib.request.urlopen(base_url + route).read().decode('utf-8')
    assert 'Fatal error' not in html and 'site-header' in html, f'WordPress render failed: {route}'
    marker = {'index.html': 'collection-heading', 'hoodie.html': 'product-title', 'pants.html': 'product-title', 'collection-one.html': 'campaign-hero', 'cart.html': 'cart-items'}[filename]
    assert marker in html, f'Missing {marker} on {route}'
    title = escape(unescape(re.search(r'<title>(.*?)</title>', html, re.S).group(1)))
    head = f'''<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow"><meta name="theme-color" content="#101010">
<title>{title}</title>
<link rel="preload" href="assets/fonts/{font}" as="font" type="font/{font_type}" crossorigin>
<link rel="stylesheet" href="assets/css/storefront.css">
{('<link rel="stylesheet" href="assets/css/peyda.css">' if peyda else '')}
</head>'''
    html = re.sub(r'<head>.*?</head>', lambda _: head, html, flags=re.S)
    for product in ('hoodie', 'pants'):
        for source in (f'{base_url}/?takav_product={product}', f'{base_url}/collection/{product}/'):
            html = html.replace(source, product + '.html')
    for view in ('collection-one', 'cart'):
        for source in (f'{base_url}/?takav_view={view}', f'{base_url}/{view}/'):
            html = html.replace(source, view + '.html')
    html = html.replace(base_url + '/wp-content/themes/takav/', '')
    html = html.replace(base_url + '/', 'index.html')
    html = re.sub(r'<script\b[^>]*>.*?</script>', '', html, flags=re.S)
    html = html.replace('</body>', '<script src="assets/js/storefront.js" defer></script>\n</body>')
    assert 'takav_product=' not in html and '/collection/hoodie/' not in html, 'Unconverted product links'
    (preview / filename).write_text(html, encoding='utf-8')
shutil.copytree(root / 'theme/takav/assets', preview / 'assets', dirs_exist_ok=True)
for name, folder, parent in [('takav-wordpress-theme.zip', root / 'theme/takav', root / 'theme'), ('takav-preview.zip', preview, output)]:
    with zipfile.ZipFile(output / name, 'w', zipfile.ZIP_DEFLATED) as archive:
        for file in sorted(folder.rglob('*')):
            if file.is_file(): archive.write(file, file.relative_to(parent))
print('Exported five pages; theme and portable preview ZIPs ready.')
