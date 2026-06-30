import json

sub_transcript_path = r"C:\Users\4B\.gemini\antigravity-ide\brain\16317780-516b-401a-905e-9d620fcc771f\.system_generated\logs\transcript.jsonl"

with open(sub_transcript_path, 'r', encoding='utf-8') as f:
    for line in f:
        try:
            data = json.loads(line)
            # Find the console logs or errors in any step
            if 'console' in str(data).lower() or 'log' in str(data).lower():
                print(f"Step {data.get('step_index')} [{data.get('type')}]:")
                content = data.get('content', '')
                if content:
                    print("  Content:", content[:500])
                tool_calls = data.get('tool_calls', [])
                if tool_calls:
                    print("  Tool calls:", json.dumps(tool_calls)[:500])
        except Exception as e:
            pass
