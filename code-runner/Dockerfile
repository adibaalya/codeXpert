FROM ubuntu:22.04

ARG DEBIAN_FRONTEND=noninteractive

ENV TZ=Asia/Kuala_Lumpur
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Install all language runtimes and tools
RUN apt update && \
    apt install -y --no-install-recommends \
        tzdata \
        python3 \
        python3-pip \
        php-cli \
        nodejs \
        npm \
        default-jdk \
        mono-mcs \
        mono-runtime \
        build-essential \
        g++ \
        gcc \
        sqlite3 \
        mysql-client \
        libmysqlclient-dev \
        && \
    rm -rf /var/lib/apt/lists/*

# Set up Python with increased recursion limit (using system Python path)
RUN mkdir -p /usr/lib/python3.10 && \
    echo "import sys; sys.setrecursionlimit(100000)" > /etc/python3.10/sitecustomize.py || \
    echo "import sys; sys.setrecursionlimit(100000)" | tee -a /usr/lib/python3/dist-packages/sitecustomize.py

# Create directories for code execution
WORKDIR /code
RUN mkdir -p /tmp/code-exec /tmp/sql-data

# Copy execution scripts
COPY run.sh /code/run.sh
COPY sql-runner.sh /code/sql-runner.sh
RUN chmod +x /code/run.sh /code/sql-runner.sh

# Set resource limits
ENV MALLOC_ARENA_MAX=2
ENV PYTHONUNBUFFERED=1
ENV NODE_OPTIONS="--max-old-space-size=128"

CMD ["./run.sh"]
