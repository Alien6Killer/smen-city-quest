FROM rnburn/nginx-opentracing:0.2.1

ADD https://github.com/Yelp/dumb-init/releases/download/v1.2.0/dumb-init_1.2.0_amd64 /usr/local/bin/dumb-init
RUN chmod +x /usr/local/bin/dumb-init

ARG environment=prod

COPY docker/nginx/default_${environment}.conf /etc/nginx/conf.d/default.conf

RUN mkdir -p /var/www/

COPY ./public /usr/src/app/public

ADD https://raw.githubusercontent.com/rlerdorf/opcache-status/master/opcache.php /usr/src/app/public

# Rewrite signal. Kubernetes always sends SIGTERM signal, nginx use SIGQUIT for graceful shutdown.
# http://nginx.org/ru/docs/control.html
ENTRYPOINT ["dumb-init", "--rewrite", "15:3"]

CMD ["nginx", "-g", "daemon off;"]
