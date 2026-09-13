import json
log_file = r'C:\Users\Lenovo\.gemini\antigravity\brain\bff629b5-ffd4-410e-bf25-7fc56c998650\.system_generated\logs\transcript_full.jsonl'
best_content = None
max_len = 0

with open(log_file, 'r', encoding='utf-8') as f:
    for line in f:
        try:
            data = json.loads(line)
            if 'tool_calls' in data:
                for call in data['tool_calls']:
                    if call['name'] == 'write_to_file' or call['name'] == 'replace_file_content':
                        args = call.get('args', {})
                        content = args.get('CodeContent', '') or args.get('ReplacementContent', '')
                        if "@section('page_title'" in content and "router" in args.get('TargetFile', '') and "show.blade.php" in args.get('TargetFile', ''):
                            if len(content) > max_len:
                                max_len = len(content)
                                best_content = content
                                print("Found candidate length " + str(max_len))
        except:
            pass

if best_content:
    with open('recovered_show2.blade.php', 'w', encoding='utf-8') as f:
        f.write(best_content)
    print("Done")
