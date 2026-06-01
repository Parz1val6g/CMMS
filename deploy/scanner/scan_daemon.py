#!/usr/bin/env python3
import http.server
import subprocess
import tempfile
import os
import json

BINARY = '/usr/local/bin/sandbox_engine'


class Handler(http.server.BaseHTTPRequestHandler):
    def log_message(self, *a):
        pass

    def do_POST(self):
        length = int(self.headers.get('Content-Length', 0))
        data = self.rfile.read(length)

        with tempfile.NamedTemporaryFile(delete=False, suffix='.tmp') as f:
            f.write(data)
            tmp = f.name

        try:
            r = subprocess.run([BINARY, tmp], capture_output=True, text=True, timeout=30)
            body = r.stdout.encode() if r.stdout else b'{"status":"error","reason":"no output"}'
        except Exception as e:
            body = json.dumps({'status': 'error', 'reason': str(e)}).encode()
        finally:
            os.unlink(tmp)

        self.send_response(200)
        self.send_header('Content-Type', 'application/json')
        self.end_headers()
        self.wfile.write(body)


if __name__ == '__main__':
    http.server.HTTPServer(('0.0.0.0', 8765), Handler).serve_forever()
