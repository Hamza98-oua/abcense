import json

transcript_path = r"C:\Users\4B\.gemini\antigravity-ide\brain\6d11ce00-de0d-4da7-94ce-65b3a4fe113d\.system_generated\logs\transcript.jsonl"

with open(transcript_path, 'r', encoding='utf-8') as f:
    for line in f:
        data = json.loads(line)
        step_type = data.get('type')
        step_idx = data.get('step_index')
        if step_type == 'BROWSER_SUBAGENT':
            print(f"Step {step_idx}:")
            print("  Result:", data.get('content')[:1000])
