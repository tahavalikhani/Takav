"""HTTP-level WordPress route checks; no browser automation required."""
import sys
from urllib.request import urlopen
from urllib.error import HTTPError
base = (sys.argv[1] if len(sys.argv)>1 else 'http://127.0.0.1:8898').rstrip('/')
for route, status, marker in [
    ('/',200,'collection-heading'),
    ('/collection/hoodie/',200,'هودی تکاو'),
    ('/collection/pants/',200,'شلوار تکاو'),
    ('/?takav_product=hoodie',200,'هودی تکاو'),
    ('/?takav_product=pants',200,'شلوار تکاو'),
    ('/collection-one/',200,'campaign-hero'),
    ('/cart/',200,'cart-items'),
    ('/?takav_view=collection-one',200,'campaign-hero'),
    ('/?takav_view=cart',200,'cart-items'),
    ('/?takav_view=missing',404,'این خط'),
    ('/collection/missing/',404,'این خط'),
    ('/?takav_product=missing',404,'این خط'),
    ('/?takav_product%5B%5D=hoodie',404,'این خط'),
    ('/?p=999999',404,'این خط'),
    ('/?p=1',200,'entry-content'),
]:
    try: response = urlopen(base+route)
    except HTTPError as error: response = error
    html = response.read().decode('utf-8')
    assert response.status == status,(route,response.status)
    assert marker in html,route
    assert 'Fatal error' not in html and '<dialog' not in html,route
    print('PASS',status,route)
