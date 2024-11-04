## Long term storage for Webform submissions
This module copies Webform submissions to a separate database for Long Term Storage (LTS).  The LTS database can then be used for data warehousing needs such as reporting and analysis.  Optionally, Personally Identifiable Information (PII) can be redacted from Webform submissions while they are copied to the LTS database.

### Setup process
- Create a database which will serve as the LTS database.
- Declare it in Drupal's settings.php using the **localgov_forms_lts** key.  Example:
  ```
  $databases['localgov_forms_lts']['default'] = [
    'database'  => 'our-long-term-storage-database',
    'username'  => 'database-username-goes-here',
    'password'  => 'database-password-goes-here',
    'host'      => 'database-hostname-goes-here',
    'port'      => '3306',
    'driver'    => 'mysql',
    'prefix'    => '',
  ];
  ```
- Install the localgov_forms_lts submodule.
- Check the module requirement report from Drupal's status page at `admin/reports/status`.  This should be under the **LocalGov Forms LTS** key.
- [Optional] If all looks good in the previous step, run `drush localgov-forms-lts:copy --force` which will copy existing Webform submissions into the LTS database.
- By default, periodic Webform submissions copying to the LTS database is disabled.  Activate it from `/admin/structure/webform/config/submissions-lts`.
- Ensure cron is running periodically.  This will copy any new Webform submissions or changes to existing Webform submissions since the last cron run or the last `drush localgov-forms-lts:copy` run.
- [Optional] Tell individual Webforms to purge submissions older than a chosen period.  This is configured for each Webform from its `Settings > Submissions > Submission purge settings` configuration section.

### Inspection
To inspect Webform submissions kept in Long term storage, look for the **LTS** tab in the Webform submissions listing page.  This is usually at `/admin/structure/webform/submissions/manage`.

### Good to know
- Each cron run copies 50 Webform submissions.  If your site is getting more than that many Webform submissions between subsequent cron runs, not all Webform submissions will get copied to LTS during a certain period.  If that happens, adjust cron run frequency.
- Files attached to Webform submissions are *not* moved to LTS.
- You can choose to redact elements with Personally Identifiable Information (PII) while they are copied to the LTS database.  For that, select *Best effort PII redactor* (or another redactor) from the `PII redactor plugin` dropdown in the LTS config page at `/admin/structure/webform/config/submissions-lts`.  At the moment, this plugin redacts all name, email, telephone, number, and various address type elements.  Additionally, any text or radio or checkbox element whose machine name (AKA Key) contains the following also gets redacted: name, mail, phone, contact_number, date_of_birth, dob_, personal_, title, nino, passport, postcode, address, serial_number, reg_number, pcn_, and driver_.
- If you are using this module in multiple instances of the same site (e.g. dev/stage/live), ensure that the database settings array points to *different* databases.  Alternatively, disable copying for the non-live environments from `/admin/structure/webform/config/submissions-lts`.  The relevant settings `localgov_forms_lts.settings:is_copying_enabled` can be [overridden](https://www.drupal.org/docs/drupal-apis/configuration-api/configuration-override-system#s-global-overrides) from settings.php.  The [config_split](https://www.drupal.org/project/config_split) module can be handy as well.
- This module is currently in experimental stage.

### Todo
- Removal of Webform submissions from LTS after a predefined period e.g. 5 years.

### Testing in DDEV

To set up testing in ddev, we'll need to set up a second database.

There are a few ways to do this, but the following seems to work.

#### 1. Add a post-start hook to your .ddev/config.yml

Edit `.ddev/config.yml` and add the following to create a new database on start.

```
hooks:
  post-start:
  - exec: mysql -uroot -proot -e "CREATE DATABASE IF NOT EXISTS localgov_forms_lts; GRANT ALL ON localgov_forms_lts.* to 'db'@'%';"
    service: db
```

#### 2. Add the database connection string to settings.php:

Edit sites/default/settings.php and add a new database connection string at the
end of the file.

```
// Database connection for localgov_forms_lts.
$databases['localgov_forms_lts']['default'] = [
  'database'  => 'localgov_forms_lts',
  'username'  => 'db',
  'password'  => 'db',
  'host'      => 'db',
  'port'      => '3306',
  'driver'    => 'mysql',
  'prefix'    => '',
];
```

#### 3. Install Adminer

Adminer is useful if you want to inspect databases and tables.

```
ddev get ddev/ddev-adminer
```

#### 4. Restart ddev

```
ddev restart
```

#### 5. Require and install the module.

```
ddev composer require localgovdrupal/localgov_forms
ddev drush si localgov_forms_lts -y
```

#### 5. Make some submissions.

For example, in LocalGov Drupal we tend to have a contact form at /form/contact.

Make a couple of submissions there.

#### 6. Run cron.

ddev drush cron

#### 7. Inspect the LTS tab

Go to /admin/structure/webform/submissions/lts

Here you should see your submissions with redacted name and email address.
