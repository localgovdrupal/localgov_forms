<?php

declare(strict_types = 1);

namespace Drupal\localgov_forms\Geocoder\Model;

use Geocoder\Location;

/**
 * Adds UPRN and display value support to Location.
 */
interface LocalgovAddressInterface extends Location {

  /**
   * Returns the Unique Property Reference Number.
   *
   * @see https://en.wikipedia.org/wiki/Unique_Property_Reference_Number
   */
  public function getUprn() :string;

  /**
   * Returns the full address in one line.
   */
  public function getDisplayName() :string;

}
