sudo rm -rf caller/volumes; docker-compose -f caller/seeder.yaml -f mariadb.yaml -f pgsql.yaml down --volumes --remove-orphans