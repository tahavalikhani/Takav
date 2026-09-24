"""Export a portable preview from the running WordPress theme and package its ZIP.

Usage: python3 tools/package.py http://127.0.0.1:8898 /path/to/outputs
"""
from pathlib import Path
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
html = urllib.request.urlopen(base_url + '/').read().decode('utf-8')
assert 'hero-title' in html, 'WordPress did not render the Takav front page'
assert 'Fatal error' not in html, 'PHP error in page output'
# Reuse exactly the WordPress-rendered body, with a portable, self-hosted head.
head = '''<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow"><meta name="theme-color" content="#101010">
<title>تکاو — پیش‌نمایش کالکشن اول</title>
<link rel="preload" href="assets/fonts/Vazirmatn.woff2" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="assets/css/storefront.css">
</head>'''
if (root / 'theme/takav/assets/fonts/Peyda-Light.ttf').exists():
    head = head.replace('assets/fonts/Vazirmatn.woff2', 'assets/fonts/Peyda-Light.ttf').replace('type="font/woff2"', 'type="font/ttf"')
    head = head.replace('</head>', '<link rel="stylesheet" href="assets/css/peyda.css">\n</head>')
html = re.sub(r'<head>.*?</head>', lambda _: head, html, flags=re.S)
html = html.replace(base_url + '/wp-content/themes/takav/', '')
html = html.replace(base_url + '/#', '#').replace(base_url + '/', './index.html')
# The exported page needs only the theme's interaction script.
html = re.sub(r'<script\b[^>]*>.*?</script>', '', html, flags=re.S)
html = html.replace('</body>', '<script src="assets/js/storefront.js" defer></script>\n</body>')
(preview / 'index.html').write_text(html, encoding='utf-8')
shutil.copytree(root / 'theme/takav/assets', preview / 'assets', dirs_exist_ok=True)
with zipfile.ZipFile(output / 'takav-wordpress-theme.zip', 'w', zipfile.ZIP_DEFLATED) as archive:
    for file in sorted((root / 'theme/takav').rglob('*')):
        if file.is_file():
            archive.write(file, file.relative_to(root / 'theme'))
with zipfile.ZipFile(output / 'takav-preview.zip', 'w', zipfile.ZIP_DEFLATED) as archive:
    for file in sorted(preview.rglob('*')):
        if file.is_file():
            archive.write(file, file.relative_to(output))
print(f'Created {preview / "index.html"}, theme ZIP and portable preview ZIP')
