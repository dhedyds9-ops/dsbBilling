import json
log_file = r'C:\Users\Lenovo\.gemini\antigravity\brain\bff629b5-ffd4-410e-bf25-7fc56c998650\.system_generated\logs\transcript_full.jsonl'
best_content = None

with open(log_file, 'r', encoding='utf-8') as f:
    for line in f:
        if 'resources/views/livewire/isp/router/show.blade.php' in line or 'resources\\\\views\\\\livewire\\\\isp\\\\router\\\\show.blade.php' in line:
            try:
                data = json.loads(line)
                if 'tool_calls' in data:
                    for call in data['tool_calls']:
                        if call['name'] == 'write_to_file' or call['name'] == 'replace_file_content':
                            args = call.get('args', {})
                            if 'show.blade.php' in args.get('TargetFile', ''):
                                if call['name'] == 'write_to_file':
                                    best_content = args.get('CodeContent')
                                    print("Found write_to_file! Length: " + str(len(best_content)))
            except:
                pass

if best_content:
    with open('recovered_show.blade.php', 'w', encoding='utf-8') as f:
        f.write(best_content)
    print("Done")
