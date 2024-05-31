## Long term storage for Webform submission

### Setup process
- Create a database which will serve as the Long term storage.
- Declare it in Drupal's settings.php using the **localgov_forms_lts** key.  Example:
  ```
  $databases['localgov_forms_lts']['default'] = [
    'database'  => 'our_longer_term_storage_database',
    'username'  => 'database_username_goes_here'
    'password'  => 'database-password-goes-here',
    'host'      => 'database-hostname-goes-here',
    'port'      => '3306',
    'driver'    => 'mysql',
    'prefix'    => '',
  ];
  ```
- Install the localgov_forms_lts submodule.
- Check the module requirement report from Drupal's status page at `admin/reports/status`.  This should be under the **LocalGov Forms LTS** key.
- If all looks good in the previous step, run `drush deploy:hook` which will copy existing Webform submissions into the Long term storage.  If you are using `drush deploy`, this will be taken care of as part of it and there would be no need for `drush deploy:hook`.
- Ensure cron is running periodically.  This will copy any new Webform submissions or changes to existing Webform submissions since deployment or the last cron run.
- [Optional] Tell individual Webforms to purge submissions older than a chosen period.  This is configured for each Webform from its `Settings > Submissions > Submission purge settings` configuration section.

### Inspection
To inspect Webform submissions kept in Long term storage, look for the "LTS" tab in the Webform submissions listing page.  This is usually at /admin/structure/webform/submissions/manage.

### Good to know
- Each cron run copies 50 Webform submissions.  If your site is getting more than that many Webform submissions between subsequent cron runs, not all Webform submissions will get copied to Long term storage during a certain period.  If that happens, adjust cron run frequency.
- Files attached to Webform submissions are *not* moved to Long term storage.
- Elements with Personally Identifiable Information (PII) are redacted.  At the moment, this includes all name, email, telephone, number, and various address type elements.  Additionally, any text or radio or checkbox element whose machine name (AKA Key) contains the following also gets redacted: name, mail, phone, contact_number, date_of_birth, dob_, personal_, title, nino, passport, postcode, address, serial_number, reg_number, pcn_, and driver_.

### Todo
- Removal of Webform submissions from Long term storage after a predefined period e.g. 5 years.
- Machine names which are indicative of PII are hardcoded within the Drupal\localgov_forms_lts\PIIRedactor class at the moment.  This needs a configuration UI.
- Automated tests.
