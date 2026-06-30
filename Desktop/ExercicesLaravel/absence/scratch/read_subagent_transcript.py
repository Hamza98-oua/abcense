import json

sub_transcript_path = r"C:\Users\4B\.gemini\antigravity-ide\brain\16317780-516b-401a-905e-9d620fcc771f\.system_generated\logs\transcript.jsonl"

with open(sub_transcript_path, 'r', encoding='utf-8') as f:
    for line in f:
        try:
            data = json.loads(line)
            step_type = data.get('type')
            step_idx = data.get('step_index')
            status = data.get('status')
            
            # Print steps of interest
            if step_type in ('BROWSER_CONSOLE_LOGS', 'CLICK_BROWSER_PIXEL', 'BROWSER_GET_DOM', 'MODEL', 'SYSTEM') or 'error' in str(data).lower():
                print(f"Step {step_idx} [{step_type}] - Status: {status}")
                content = data.get('content', '')
                if content:
                    print("  Content preview:", content[:300].replace('\n', ' '))
                # If there are tool calls and their results
                tool_calls = data.get('tool_calls', [])
                if tool_calls:
                    print("  Tool calls:", json.dumps(tool_calls)[:300])
        except Exception as e:
            print("Error parsing line:", e)
