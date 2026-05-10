import os
import glob

base_dir = r"c:\xampp\htdocs\Esprit-PW-2A32-2526-TalentBridge-job\Views"

replacements = [
    ('background: rgba(255,255,255,0.02)', 'background: var(--card-bg)'),
    ('background: rgba(255,255,255,0.05)', 'background: var(--input-bg)'),
    ('background: rgba(255, 255, 255, 0.05)', 'background: var(--input-bg)'),
    ('background: rgba(255,255,255,0.1)', 'background: var(--hover-bg)'),
    ('background: rgba(0,0,0,0.4)', 'background: var(--input-bg)'),
    ('border: 1px solid rgba(255,255,255,0.05)', 'border: 1px solid var(--border-color)'),
    ('border: 1px solid rgba(255,255,255,0.1)', 'border: 1px solid var(--border-color)'),
    ('border-color: rgba(255,255,255,0.1)', 'border-color: var(--border-color)'),
    ('color: white', 'color: var(--text-main)'),
    ('color: #ffffff', 'color: var(--text-main)'),
    ('color: #fff', 'color: var(--text-main)'),
    ('background: #050812', 'background: var(--bg-dark)'),
    ('background: #111827', 'background: var(--card-bg)'),
    ('background: #0f172a', 'background: var(--bg-dark)'),
    ('background: #020617', 'background: var(--bg-dark)'),
    ('style="color: var(--text-main);"', 'style="color: var(--text-main);"'), # No-op just in case
]

for root, _, files in os.walk(base_dir):
    for f in files:
        if f.endswith('.php') or f.endswith('.html'):
            filepath = os.path.join(root, f)
            with open(filepath, 'r', encoding='utf-8') as file:
                content = file.read()
            
            orig_content = content
            for old, new in replacements:
                content = content.replace(old, new)
            
            if content != orig_content:
                with open(filepath, 'w', encoding='utf-8') as file:
                    file.write(content)
                print(f"Updated {f}")

print("Done fixing inline styles.")
