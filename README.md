# Chilli Boilerplate

Based on Craft CMS and Vite for frontend tooling

## Setup
Setup your nitro container based upon this boilerplate. Make sure to use **PHP 7.4** to use **webp** images. This is because Imagick has not yet been updated to work with PHP 8.

    nitro create wearechilli/vite-generator-chilli your-site-folder

Install craft:

    nitro craft install/craft

## Vite

You must start Vite from within your container, to start vite run:

    nitro ssh
    npm run dev

One issue currently in Vite is when referencing assets processed in Vite such as images in CSS, [click here](https://github.com/vitejs/vite/issues/2196) for more info. A workaround is to set the base url when Vite dev server is running. In `templates/_layout/base.twig` see:

    <base href="{{ alias('@viteBaseUrl') }}">

Because of this you  must use absolute url's in your app everywhere.

## Deploying

Deploying is done though Combell AutoGit. Make sure AutoGit is enabled in the project's service pack. Add the combell remote branch.

    git remote add combell domainbe@ssh.domain.be:auto.git

Push to your remote to deploy the current branch.

    git push combell

## Shipit

Use shipit to pull remote database and assets. First copy the `.env.db.example` for each deployed branch, for example `.env.db.staging`.

To pull the remote database or assets run:

    npx shipit staging db:pull
    npx shipit staging assets:pull

## Code Examples

Make sure to check the `templates/examples.twig` file for frontend things that are included in the boilerplate such as: SVG's, image transforms, modals, menu overlays, form elements, etc.

## A word about the Craft Queue & Combell

Craft executes all kind of tasks via a queue system. Jobs (aka tasks) are added to the queue, and get executed. Unfortunately this queue only starts when someone logs into the control panel.

The queue can also be started through the craft executable. Since Combell does not allow plugins to start php processes, we have to do this via cron:

    sss user@xxx.webhosting.be
    nano /etc/crontab

Create a cron job that executes the queue every 5 minutes like so:

    */5 * * * * /usr/bin/env php checkout/master/current/craft queue/run
