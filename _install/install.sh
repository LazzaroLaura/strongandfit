#!/bin/bash
# chargement de la configuration
. ./_configuration.sh


wp_cli_install() {
    curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
    chmod +x wp-cli.phar
    sudo mv wp-cli.phar /usr/local/bin/wp
}

wp_cli_config() {
    echo "💚 wp-cli configuration";
    echo "path: $WORDPRESS_FOLDER" > wp-cli.yml;
    echo "apache_modules:" >> wp-cli.yml;
    echo "  - mod_rewrite" >> wp-cli.yml;
}

replace_in_file() {
    php -r "file_put_contents('$3', str_replace('$1', '$2', file_get_contents('$3')));";
}

bdd_setup() {
    echo "💚 Database setup";

    read -p "☠️ Dropping database $WORDPRESS_BDD. Are you sure ? (N)" DROP_DATABASE
    echo    # (optional) move to a new line
    if [[ $DROP_DATABASE =~ ^[Yy]$ ]]
    then
        #clear bdd
        echo "    ☠️ Dropping database $WORDPRESS_BDD";
        mysql -h$MYSQL_HOST -u$MYSQL_USER -p$MYSQL_PASSWORD --execute="DROP DATABASE $WORDPRESS_BDD;"
    fi

    #création de la bdd
    echo "    👌 Creating database $WORDPRESS_BDD";
    mysql -h$MYSQL_HOST -u$MYSQL_USER -p$MYSQL_PASSWORD --execute="CREATE DATABASE $WORDPRESS_BDD CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
}

wp_config() {
    echo "💚 Building wp-config.php";

    if [[ -f wp-config.php ]]
    then
        echo "💚 Backup of existing wp-config.php";
        cp wp-config.php wp-config.php.back;
    fi


    cp wp/wp-config-sample.php wp-config.php;

    replace_in_file 'database_name_here' $WORDPRESS_BDD 'wp-config.php';
    replace_in_file 'username_here' $MYSQL_USER 'wp-config.php';
    replace_in_file 'password_here' $MYSQL_PASSWORD 'wp-config.php';
    replace_in_file "define( \'WP_DEBUG\', false )" "define( \'WP_DEBUG\', true )" 'wp-config.php';

    replace_in_file "table_prefix = \'wp_\'" "table_prefix = \'$WORDPRESS_TABLE_PREFIX\'" 'wp-config.php';


    php -r "
        \$config = file_get_contents(__DIR__ . '/wp-config.php');

        \$config = preg_replace_callback('/put your unique phrase here/', function(\$matches) {
            return password_hash(uniqid(), PASSWORD_DEFAULT );
        }, \$config);

        file_put_contents(__DIR__ . '/wp-config.php', \$config);
    ";


    php -r "file_put_contents('wp-config.php', str_replace('/* That\'s all, stop editing! Happy publishing. */', \"

    define('WP_HOME', rtrim ( '$WORDPRES_URL', '/' ));
    define('WP_SITEURL', WP_HOME . '/$WORDPRESS_FOLDER');
    define('WP_CONTENT_URL', WP_HOME . '/wp-content');
    define('WP_CONTENT_DIR', __DIR__ . '/wp-content');
    define('FS_METHOD','direct');
    /* That\'s all, stop editing! Happy publishing. */
    \", file_get_contents('wp-config.php')));";

}


# =======================================================================================================
# =======================================================================================================


CURRENT_PATH="$(pwd)"
INSTALL_PATH=$CURRENT_PATH"/"$1;
echo "$INSTALL_PATH";

if [[ ! -d $INSTALL_PATH ]]
then
    echo "💚 Creating folder $INSTALL_PATH ";
    mkdir $INSTALL_PATH;
fi


# vérification est ce que wp-cli est installé
if [ -f "/usr/local/bin/wp" ]; then
    echo "✔️ wp-cli already installed";
else
    echo "💚 Install wp-cli";
    wp_cli_install;
fi

bdd_setup;



if [[ -f "$INSTALL_PATH/wp-config.php" ]]
then
    echo "💚 gitignore already exists";
else
    echo "💚 Adding .gitignore";
    cp "$CURRENT_PATH/provision/.gitignore" "$INSTALL_PATH";
fi


if [[ -f "$INSTALL_PATH/composer.json" ]]
then
    echo "💚 composer.json file exists";
else
    echo "💚 creating composer.json file";
    cp "$CURRENT_PATH/provision/composer.json" "$INSTALL_PATH";
fi


echo "💚 Composer install";
cd $INSTALL_PATH;
composer install;


if [[ -f "$INSTALL_PATH/index.php" ]]
then
    echo "💚 index.php file exists";
else
    echo "💚 Building index.php";
    cp $WORDPRESS_FOLDER/index.php .;
    php -r "file_put_contents('index.php', str_replace('/wp-blog-header.php', '/$WORDPRESS_FOLDER/wp-blog-header.php', file_get_contents('index.php')));";
fi


wp_config;

wp_cli_config;


# https://developer.wordpress.org/cli/commands/core/
echo "💚 Wordpress installation";
wp core install --url="$WORDPRES_URL" --title="$WORDPRES_SITE_NAME" --admin_user="$WORDPRES_ADMIN_NAME" --admin_password="$WORDPRES_ADMIN_PASSWORD" --admin_email="$WORDPRES_ADMIN_EMAIL" --skip-email;

echo "💚 Changing folder rigths";
composer run chmod

echo "💚 Generating .htaccess";
composer run activate-htaccess

echo "💚 Plugins activations";
composer run activate-plugins


echo "👌 Install successfull.";
echo "      🏠 Front URL : $WORDPRES_URL";
echo "      ⚙️ Back URL : $WORDPRES_URL/$WORDPRESS_FOLDER/wp-admin";

cd $CURRENT_PATH