# for dev-environment, not production

FROM debian AS base
MAINTAINER emilbratt

EXPOSE 80


# skip possible prompts
ARG DEBIAN_FRONTEND=noninteractive

# install every package needed
RUN apt-get update && \
    apt-get upgrade -y && \
    apt-get install apache2 -y && \
    apt-get install php -y && \
    apt-get install python -y && \
    apt-get install libapache2-mod-php -y && \
    apt-get install sqlite3 -y && \
    apt-get install php7.3-sqlite3 -y && \
    apt-get install python3-pip -y && \
    python3 -m pip install --upgrade pip && \
    python3 -m pip install --upgrade Pillow

# change uid for www-data that runs inside the container to my own uid
RUN usermod -u 1000 www-data

ENTRYPOINT apache2ctl -D FOREGROUND
