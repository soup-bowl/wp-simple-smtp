# Development quickstart with Docker
This plugin has designed to be ready to go with [Docker] (using [docker-compose][compose]) very quickly. All you need is Docker and docker-compose to be installed on **any platform**.

If you're using **Visual Studio Code**, the [Docker extension][ext] removes a lot of the CLI steps.

## Start-up
Simply run the following code in the root of the cloned Git directory.

```
docker-compose up --build -d
```
This does the following:
* Reads the docker-compose.yml root file to see what images and setups we need (web, db, mail mocker).
* `build` instructs compose to re-build the custom images. Not always needed, but essential if you change the Dockerfile.
* `d` returns the TTL back to you. If omitted, you will start seeing on-the-fly logs from each container.

If no errors occur, you'll now have the following local bindings:
* WordPress on port 80 & 443 (default) - visting http://localhost should load up the WordPress installer.
* Database with an auto-login phpMyAdmin instance on http://localhost:8082.
* Mail mocker, with the SMTP server on port 1025 (8083 for outside-container usage), and the GUI on http://localhost:8081.

## Quickstart Script
To speed this up even more, I've included a small script that interfaces with [WP CLI][cli] to bypass the 5 minute install process. This will give you a basic functioning WordPress site with typically default development installation choices. The username is **admin** and the password is **password**. 

* For a regular install, run `docker-compose exec www quickstart`.
* For a multisite instance, run `docker-compose exec www quickstart ms`.

This **does not setup SMTP**, to avoid impeding testing procedures. You'll still need to do that.

[docker]: https://www.docker.com/
[compose]: https://docs.docker.com/compose/
[ext]: https://marketplace.visualstudio.com/items?itemName=ms-azuretools.vscode-docker
[cli]: https://wp-cli.org/
