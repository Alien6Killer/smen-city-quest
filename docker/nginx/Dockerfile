FROM nginx

ARG environment=prod

COPY docker/nginx/default_${environment}.conf /etc/nginx/conf.d/default.conf
COPY docker/nginx/nginx.conf /etc/nginx/
