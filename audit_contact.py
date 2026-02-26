import re
with open('contact.html','r',encoding='utf-8') as f:
    content=f.read()
print('='*70)
print('DRAGONSHIELD - CONTACT.HTML SECURITY AUDIT')
print('='*70)
issues=[]
print('\n[1] HEAD SECURITY')
print('-'*40)
if 'Content-Security-Policy' not in content:
    issues.append('SEC-01: Missing CSP meta tag')
    print('  [CRITICAL] No CSP meta tag')
if 'X-Content-Type-Options' not in content:
    issues.append('SEC-02: Missing X-Content-Type-Options')
    print('  [HIGH] No X-Content-Type-Options')
