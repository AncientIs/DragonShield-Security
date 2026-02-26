import os, re, json

def read_file(path):
    with open(path, 'r', encoding='utf-8') as f:
        return f.read()

def write_file(path, content):
    with open(path, 'w', encoding='utf-8', newline='\n') as f:
        f.write(content)

issues_found = []
fixes_applied = []

# List all files
html_files = [f for f in os.listdir('.') if f.endswith('.html') and f != 'index_fixed.html']
css_files = [os.path.join('css', f) for f in os.listdir('css') if f.endswith('.css')]
js_files = [os.path.join('js', f) for f in os.listdir('js') if f.endswith('.js')]
php_files = [f for f in os.listdir('.') if f.endswith('.php')]
php_files += [os.path.join('includes', f) for f in os.listdir('includes') if f.endswith('.php')]

print("=== FILES IN REPO ===")
print(f"HTML: {html_files}")
print(f"CSS: {css_files}")
print(f"JS: {js_files}")
print(f"PHP: {php_files}")

# ==========================================
# AUDIT HTML FILES
# ==========================================
for hf in html_files:
    content = read_file(hf)
    print(f"\n=== AUDITING {hf} ===")
    
    # 1. HTML5 Compliance
    if not content.strip().startswith('<!DOCTYPE html>'):
        issues_found.append(f"{hf}: Missing HTML5 doctype")
    else:
        print(f"  [OK] HTML5 doctype")
    
    if '<html lang="en">' not in content:
        issues_found.append(f"{hf}: Missing lang attribute")
    else:
        print(f"  [OK] lang attribute")
    
    if '<meta charset="UTF-8">' not in content:
        issues_found.append(f"{hf}: Missing charset")
    else:
        print(f"  [OK] charset UTF-8")
    
    if 'viewport' not in content:
        issues_found.append(f"{hf}: Missing viewport meta")
    else:
        print(f"  [OK] viewport meta")
    
    # 2. Security Headers
    has_csp = 'Content-Security-Policy' in content
    has_xcto = 'X-Content-Type-Options' in content
    has_xfo = 'X-Frame-Options' in content
    has_referrer = 'referrer' in content.lower()
    print(f"  CSP: {has_csp}, XCTO: {has_xcto}, XFO: {has_xfo}, Referrer: {has_referrer}")
    
    if not has_csp:
        issues_found.append(f"{hf}: Missing CSP meta tag")
    if not has_xcto:
        issues_found.append(f"{hf}: Missing X-Content-Type-Options")
    if not has_xfo:
        issues_found.append(f"{hf}: Missing X-Frame-Options")
    
    # 3. SEO
    has_title = bool(re.search(r'<title>.+</title>', content))
    has_desc = 'meta name="description"' in content
    has_og = 'og:' in content
    print(f"  Title: {has_title}, Description: {has_desc}, OpenGraph: {has_og}")
    
    if not has_title:
        issues_found.append(f"{hf}: Missing title tag")
    if not has_desc:
        issues_found.append(f"{hf}: Missing meta description")
    if not has_og:
        issues_found.append(f"{hf}: Missing Open Graph tags")
    
    # 4. WCAG 2.1 Accessibility
    has_skip = 'skip' in content.lower() and 'main' in content.lower()
    has_main_landmark = '<main' in content
    has_nav_aria = 'role="navigation"' in content or 'aria-label' in content
    has_focus_visible = 'focus-visible' in content or 'focus' in content.lower()
    print(f"  SkipLink: {has_skip}, MainLandmark: {has_main_landmark}, NavAria: {has_nav_aria}")
    
    if not has_main_landmark:
        issues_found.append(f"{hf}: Missing <main> landmark element")
    if not has_skip:
        issues_found.append(f"{hf}: Missing skip-to-main-content link")
    
    # Check for images without alt
    imgs = re.findall(r'<img[^>]*>', content)
    for img in imgs:
        if 'alt=' not in img:
            issues_found.append(f"{hf}: Image missing alt attribute: {img[:60]}")
    
    # 5. Performance - script loading
    scripts = re.findall(r'<script[^>]*src=[^>]*>', content)
    for s in scripts:
        if 'defer' not in s and 'async' not in s:
            issues_found.append(f"{hf}: Script missing defer/async: {s[:60]}")
            print(f"  [ISSUE] Script missing defer/async: {s[:60]}")
    
    # 6. Inline event handlers
    inline_handlers = re.findall(r'on\w+\s*=\s*["\']', content)
    if inline_handlers:
        issues_found.append(f"{hf}: Has inline event handlers: {inline_handlers}")
    else:
        print(f"  [OK] No inline event handlers")

# ==========================================
# AUDIT CSS
# ==========================================
for cf in css_files:
    content = read_file(cf)
    print(f"\n=== AUDITING {cf} ===")
    
    has_focus = ':focus' in content
    has_focus_visible = ':focus-visible' in content
    has_media_query = '@media' in content
    has_prefers_reduced = 'prefers-reduced-motion' in content
    has_prefers_color = 'prefers-color-scheme' in content
    
    deprecated = []
    if '-webkit-appearance' in content and 'appearance' not in content.replace('-webkit-appearance',''):
        deprecated.append('-webkit-appearance without standard')
    
    print(f"  Focus styles: {has_focus}")
    print(f"  Focus-visible: {has_focus_visible}")
    print(f"  Media queries: {has_media_query}")
    print(f"  Prefers-reduced-motion: {has_prefers_reduced}")
    print(f"  Prefers-color-scheme: {has_prefers_color}")
    
    if not has_focus:
        issues_found.append(f"{cf}: Missing :focus styles for accessibility")
    if not has_focus_visible:
        issues_found.append(f"{cf}: Missing :focus-visible styles")
    if not has_prefers_reduced:
        issues_found.append(f"{cf}: Missing prefers-reduced-motion media query")

# ==========================================
# AUDIT JS
# ==========================================
for jf in js_files:
    content = read_file(jf)
    print(f"\n=== AUDITING {jf} ===")
    
    has_eval = 'eval(' in content
    has_innerhtml = 'innerHTML' in content
    has_doc_write = 'document.write' in content
    has_strict = "'use strict'" in content or '"use strict"' in content
    has_var = bool(re.search(r'\bvar\b', content))
    has_const_let = 'const ' in content or 'let ' in content
    
    print(f"  eval(): {has_eval}")
    print(f"  innerHTML: {has_innerhtml}")
    print(f"  document.write: {has_doc_write}")
    print(f"  'use strict': {has_strict}")
    print(f"  Uses var: {has_var}")
    print(f"  Uses const/let: {has_const_let}")
    
    if has_eval:
        issues_found.append(f"{jf}: Uses eval() - security risk")
    if has_innerhtml:
        issues_found.append(f"{jf}: Uses innerHTML - XSS risk")
    if has_doc_write:
        issues_found.append(f"{jf}: Uses document.write")
    if not has_strict:
        issues_found.append(f"{jf}: Missing 'use strict'")
    if has_var:
        issues_found.append(f"{jf}: Uses var instead of const/let")

print("\n\n=== ISSUES SUMMARY ===")
for i, issue in enumerate(issues_found):
    print(f"  {i+1}. {issue}")
print(f"\nTotal issues: {len(issues_found)}")
