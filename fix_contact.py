import re
with open("contact.html","r",encoding="utf-8") as f:
    c = f.read()
orig = c
fixes = 0
print("="*70)
print("DRAGONSHIELD FIX SCRIPT - CONTACT.HTML")
print("="*70)

# Fix 1: Add object-src and upgrade-insecure-requests to CSP
old_csp = 'form-action \'self\';">'
new_csp = 'form-action \'self\'; object-src \'none\'; upgrade-insecure-requests;">'
if old_csp in c:
    c = c.replace(old_csp, new_csp)
    fixes += 1
    print("[FIX 1] Added object-src none and upgrade-insecure-requests to CSP")

# Fix 2: Add pattern to full-name input
old_name = 'name="full-name"'
new_name = 'name="full-name" pattern="[A-Za-z .\'-]{2,100}"'
if old_name in c and 'pattern=' not in c.split('id="full-name"')[0].split('<input')[-1]:
    c = c.replace('id="full-name" name="full-name"', 'id="full-name" name="full-name" pattern="[A-Za-z .\'\\-]{2,100}"')
    fixes += 1
    print("[FIX 2] Added pattern validation to full-name input")

# Fix 3: Add pattern to company input
if 'id="company"' in c and 'pattern=' not in c.split('id="company"')[1][:100]:
    c = c.replace('id="company" name="company"', 'id="company" name="company" pattern="[A-Za-z0-9 .&,\'\\-]{2,200}"')
    fixes += 1
    print("[FIX 3] Added pattern validation to company input")

# Fix 4: Remove sensitive CSRF comment
if "<!-- CSRF Token Placeholder -->" in c:
    c = c.replace("<!-- CSRF Token Placeholder -->", "")
    fixes += 1
    print("[FIX 4] Removed CSRF placeholder comment (info disclosure)")

# Fix 5: Replace static CSRF token
if 'value="CSRF_TOKEN_PLACEHOLDER"' in c:
    c = c.replace('value="CSRF_TOKEN_PLACEHOLDER"', 'value="" data-csrf="dynamic"')
    fixes += 1
    print("[FIX 5] Marked CSRF token for dynamic generation")

# Fix 6: Add defer to script tag
if '<script src="js/main.js"></script>' in c:
    c = c.replace('<script src="js/main.js"></script>', '<script src="js/main.js" defer></script>')
    fixes += 1
    print("[FIX 6] Added defer to main.js script tag")

# Fix 7: Obfuscate email addresses
if "contact@dragonshieldsecurity.com" in c:
    c = c.replace("contact@dragonshieldsecurity.com", "contact [at] dragonshieldsecurity [dot] com")
    fixes += 1
    print("[FIX 7] Obfuscated contact email address")

if "sales@dragonshieldsecurity.com" in c:
    c = c.replace("sales@dragonshieldsecurity.com", "sales [at] dragonshieldsecurity [dot] com")
    fixes += 1
    print("[FIX 8] Obfuscated sales email address")

# Fix 9: Add autocomplete=off to form
if 'id="contact-form"' in c and 'autocomplete=' not in c.split('id="contact-form"')[1][:50]:
    c = c.replace('id="contact-form"', 'id="contact-form" autocomplete="off"')
    fixes += 1
    print("[FIX 9] Added autocomplete=off to contact form")

# Fix 10: Add required to timeline select
if 'id="timeline"' in c and 'required' not in c.split('id="timeline"')[1][:80]:
    c = c.replace('id="timeline" name="timeline">', 'id="timeline" name="timeline" required aria-required="true">')
    fixes += 1
    print("[FIX 10] Added required to timeline select")

# Fix 11: Add honeypot field for spam protection
honeypot = '            <div style="display:none" aria-hidden="true"><input type="text" name="website" tabindex="-1" autocomplete="off"></div>'
if 'name="website"' not in c:
    c = c.replace('name="_csrf_token"', 'name="_csrf_token">\n' + honeypot + '\n            <input type="hidden"', 1)
    fixes += 1
    print("[FIX 11] Added honeypot field for spam/bot protection")

# Fix 12: Add novalidate warning comment for developers
if "process_contact.php" in c:
    c = c.replace('action="process_contact.php"', 'action="process_contact.php" data-validate="true"')
    fixes += 1
    print("[FIX 12] Added data-validate attribute for JS validation hook")

# Save fixed file
with open("contact_fixed.html", "w", encoding="utf-8") as f:
    f.write(c)
print("\n" + "="*70)
print(f"TOTAL FIXES APPLIED: {fixes}")
print("Fixed file saved as: contact_fixed.html")
print("="*70)
