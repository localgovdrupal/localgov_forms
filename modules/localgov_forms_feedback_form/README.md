# LocalGov Forms - Feedback Form

## What is it?
This is a small module that creates a feedback for for your LocalGov Drupal website.

It asks a simple question: "Was this page helpful?".

If someone answers "Yes", they are asked if they'd like to share what is good about this page. If they answer "No", they have the opportunity to say how the page can be improved.

## How to use it.
- Install it like any other Drupal module from the "Extend" page.
- Add a block of type "Webform" to your site via the `Admin > Structure > Blocks` page.
- When the form is submitted, results will be stored in the database. They can be accessed by going to `Admin > Structure > Webforms`, clicking on the "LocalGov Forms Feedback Form", and then choosing "Results".

## Considerations
When you enable this module, the form that is created is set to store data in your website's database. This is the default datastore for webforms. However, this may not be where your data protection officer wishes you to store webform submissions. Check with your DPO before deploying this to a live site, or else edit the form configuration to store the data somewhere else - a CRM, an offsite backup, a separate database, etc.

Maintainers:
- Mark Conroy
