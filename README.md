# HTML Crawler
<p>Simple php symfony webcrawler that can be executed locally by commands.<br>
Service <strong>WebCrawler</strong>> searches a website for all A tags and lists them<br>
Service <strong>SitemapCrawler</strong> searches an external sitemap for links and checks their status code. The result is saved locally as a csv file.</p>
<p>Both services can be called by commands in the PHP Docker container:<br>
app:fetch-sitemap<br>
app:fetch-website</p>

## use docker 
docker-compose down && docker-compose up --build

## run composer to install php packages
docker-compose run --rm composer install

## commands
### app:fetch-sitemap           
Fetches a sitemap, checks all links, and outputs their status codes in the console and csv

### app:fetch-website           
Fetches the content of a website and return all internal links with statuscode and all external links

## run commands in docker
docker-compose exec php bash
php bin/console <command>