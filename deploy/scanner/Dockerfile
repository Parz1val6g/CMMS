FROM ubuntu:24.04

RUN apt-get update && apt-get install -y \
    gcc make git python3 clamav docker.io \
    && rm -rf /var/lib/apt/lists/*

RUN git clone https://github.com/Parz1val6g/file-analysis-sandbox.git /tmp/sandbox \
    && cd /tmp/sandbox/sandbox \
    && make \
    && cp sandbox_engine /usr/local/bin/sandbox_engine \
    && rm -rf /tmp/sandbox

COPY deploy/scanner/scan_daemon.py /scan_daemon.py

EXPOSE 8765

CMD ["python3", "/scan_daemon.py"]
