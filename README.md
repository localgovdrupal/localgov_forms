# LocalGovDrupal Forms

Provides additional configuration, styling and components for the Drupal Webform module.

## Additional components

* LocalGov Forms Date - A date input field based on the [GDS Date Input pattern](https://design-system.service.gov.uk/components/date-input/)
* LocalGov address lookup - Webform element with a configurable address lookup backend.  Geocoder plugins act as backends.

## Dependencies
The geocoder-php/nominatim-provider package is necessary to run automated tests:
```
$ composer require --dev geocoder-php/nominatim-provider
```

The localgovdrupal/localgov_geo and localgovdrupal/localgov_os_places_geocoder_provider packages are needed to use the Ordnance Survey Places API-based address lookup plugin.  Once these packages are installed, the *Localgov OS Places* plugin will become available for selection from the Localgov address lookup element's configuration form.
