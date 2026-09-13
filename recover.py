import json

log_file = r'C:\Users\Lenovo\.gemini\antigravity\brain\bff629b5-ffd4-410e-bf25-7fc56c998650\.system_generated\logs\transcript_full.jsonl'

longest_replacement = ""
with open(log_file, 'r', encoding='utf-8') as f:
    for line in f:
        try:
            data = json.loads(line)
            if 'tool_calls' in data:
                for call in data['tool_calls']:
                    if call['name'] == 'replace_file_content':
                        args = call.get('args', {})
                        file_path = args.get('TargetFile', '')
                        if 'show.blade.php' in file_path and 'router' in file_path:
                            replacement = args.get('ReplacementContent', '')
                            if len(replacement) > len(longest_replacement):
                                longest_replacement = replacement
        except:
            pass

if longest_replacement:
    with open('recovered_show.blade.php', 'w', encoding='utf-8') as f:
        f.write(longest_replacement)
    print("Recovered from replace_file_content! Length: " + str(len(longest_replacement)))
else:
    print("Could not find.")
