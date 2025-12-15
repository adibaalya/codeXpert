FROM ubuntu:22.04

ARG DEBIAN_FRONTEND=noninteractive

ENV TZ=Asia/Kuala_Lumpur
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN apt update && \
    apt install -y --no-install-recommends \
        tzdata \
        python3 \
        php-cli \
        nodejs \
        default-jdk \
        mono-mcs \
        mono-runtime \
        build-essential \
        g++ \
        gcc && \
    rm -rf /var/lib/apt/lists/*

WORKDIR /code
COPY run.sh /code/run.sh
RUN chmod +x /code/run.sh

CMD ["./run.sh"]
