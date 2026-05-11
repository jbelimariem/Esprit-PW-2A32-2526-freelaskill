import os

base = r'c:\xampp\htdocs\Esprit-PW-2A32-2526-TalentBridge-job'
files = [
    r'Views\Frontoffice\front_contrat_index.php',
    r'Views\Frontoffice\front_contrat_list.php',
    r'Views\Frontoffice\front_contrat_form.php',
    r'Views\Frontoffice\front_contrat_details.php',
    r'Views\Frontoffice\front_rules_index.php',
    r'Views\Frontoffice\front_rules_list.php',
    r'Views\Frontoffice\front_rules_form.php',
]

for f in files:
    path = os.path.join(base, f)
    with open(path, 'rb') as fh:
        raw = fh.read()
    try:
        text = raw.decode('utf-8')
        # Check if it contains double-encoded sequences (UTF-8 bytes interpreted as Latin-1)
        if 'Ã©' in text or 'Ã¨' in text or 'â€' in text or 'Ã ' in text:
            # Re-encode as latin-1 bytes, then decode as utf-8 to fix double-encoding
            fixed = text.encode('latin-1').decode('utf-8')
            with open(path, 'w', encoding='utf-8', newline='') as fh:
                fh.write(fixed)
            print('FIXED:', f)
        else:
            print('OK (no corruption):', f)
    except Exception as e:
        print('ERROR:', f, str(e))

print('Done.')
