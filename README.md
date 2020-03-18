# Cockpit CMS library skeleton

If you want to keep your docs root clean while using Cockpit, you can use this skeleton to rearrange the file structure and to include cockpit as a library. You can use cockpit like before - with some advantages:

* Add your own favicon.
* Add your own files to the root without messing up the cockpit installation.
* You can use your root as an own git repository.
* ...

## File structure

After doing all steps described under [Installation](#installation), your file structure should look like this:

```text
.
├── cpdata
│   ├── addons
│   ├── config
│   └── storage
├── lib
│   └── cockpit
│   .htaccess
│   bootstrap.php
│   cp
│   defines.php
│   index.php
│   ...
```

## Installation

Navigate to your docs root or to the sub folder, where you want to run cockpit.

```bash
# cd into docs root
cd ~/html
```

Copy the files inside `/html` from this repository into your docs root:

```bash
# load repo, unzip, copy into current dir and delete temp files
wget -q -O temp.zip https://github.com/raffaelj/cockpit-lib-skeleton/archive/master.zip
unzip -q temp.zip && mv cockpit-lib-skeleton-master/html/{.[!.],}* .

# optional: delete the unzipped folder
# rm -r cockpit-lib-skeleton-master

# delete temp file
rm temp.zip
```

Copy Cockpit into `/lib/cockpit`:

```bash
# clone latest cockpit (next branch)
git clone https://github.com/agentejo/cockpit.git lib/cockpit

# change environment root
cat > defines.php <<EOF
<?php
define('COCKPIT_ENV_ROOT', str_replace(DIRECTORY_SEPARATOR, '/', __DIR__) . '/cpdata');
EOF
```

Create `cpdata` folder, create `config` and `addons` directories in it and copy the core storage folder. You can delete the `.gitignore` files.

```bash
mkdir -p cpdata/{config,addons}

# copy storage folder and remove .gitignore files
cp -r lib/cockpit/storage cpdata
find cpdata/storage/ -name .gitignore -exec rm {} +
```

Optional: clone addons into `/cpdata/addons`, e. g.:

```bash
git clone https://github.com/raffaelj/cockpit_UniqueSlugs.git cpdata/addons/UniqueSlugs
```

Create a config file:

```bash
cat > cpdata/config/config.yaml <<EOF
app.name: cp lib
EOF
```

You can't run the install routine in the browser via `example.com/lib/cockpit/install`, because the access is denied via `.htaccess` and the constant `COCKPIT_ENV_ROOT` wouldn't be recognized. Create the first admin user with password "admin" via command line instead.

```bash
# generate hashed password
./cp account/generate-password --passwd admin

# copy/paste the output and create account
./cp account/create --user admin --email 'test@example.com' --passwd '$2y$10$fGd3stGM8YASqLsTCQWr0uq/OikGiZeUTXqynqJYMKdzFuPV9ytTK'
```

Now open your site in the web browser, e. g. `example.com` and you should be redirected to `example.com/auth/login`, where you can login with admin/admin.

## How to build the skeleton from scratch

Copy some files from the orginal cockpit repository:

* `cp`
* `index.php`
* `.htaccess`

Modify `.htaccess` and add the following line(s) to the top of the file

```
# Deny direct access to library files
RedirectMatch 403 ^.*/lib/(.*)\.php$
```

Create a file `bootstrap.php`:

* include optional `defines.php`
* define `COCKPIT_DOCS_ROOT`, `COCKPIT_BASE_URL` and `COCKPIT_BASE_ROUTE`. You can copy the snippets from the core bootstrap file.
* bootstrap Cockpit with `require(__DIR__.'/lib/cockpit/bootstrap.php');`
* hook into the `app.layout.header` event and add a js snippet to fix wrong paths in the admin ui.

```php
$cockpit->on('app.layout.header', function() {
    echo '<script>
        App.base_url = (App.base_url + "/lib/cockpit/").replace(/\/$/, "");
        App.env_url = "'. $this->pathToUrl(COCKPIT_ENV_ROOT) .'";
        App.base = function(url) {
            return url.indexOf("/addons") === 0 || url.indexOf("/config") === 0 ? this.env_url+url : this.base_url+url;
        };
        App.route = function(url) {
            if (url.indexOf("/assets") === 0 && url.indexOf("/assetsmanager") !== 0) {
                return this.base_route+"/lib/cockpit"+url;
            }
            if (url.indexOf("/addons") === 0 || url.indexOf("/config") === 0) {
                return this.env_url+url;
            }
            return this.base_route+url;
        };
    </script>';
});
```

## Credits/License

Some files and snippets are copied from the core Cockpit CMS, author: Artur Heinze, www.agentejo.com, MIT License

Everything else: Raffael Jesche, MIT License
