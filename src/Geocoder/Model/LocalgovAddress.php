<?php

declare(strict_types = 1);

namespace Drupal\localgov_forms\Geocoder\Model;

use Geocoder\Model\Address;

/**
 * A LocalgovAddress includes a UPRN value and display name...
 *
 * ...in addition to everything in a Geocoder Address.
 *
 * UPRN = Unique Property Reference Number.
 */
class LocalgovAddress extends Address implements LocalgovAddressInterface {

  /**
   * Unique Property Reference Number.
   *
   * @var string
   *
   * @see https://en.wikipedia.org/wiki/Unique_Property_Reference_Number
   */
  protected $uprn = '';

  /**
   * The full address in one line.
   *
   * @var string
   */
  protected $displayName = '';

  /**
   * Flat number.
   *
   * @var string
   */
  protected $flat = '';

  /**
   * House name.
   *
   * @var string
   */
  protected $houseName = '';

  /**
   * Organisation.
   *
   * @var string
   */
  protected $org = '';


  /**
   * All numeric Ordnance Survey National Grid reference.
   *
   * @var OsGridRef
   *
   * @see https://en.wikipedia.org/wiki/Ordnance_Survey_National_Grid#All-numeric_grid_references
   */
  protected $osGridRef;

  /**
   * As it says on the tin.
   */
  public function getUprn() :string {

    return $this->uprn;
  }

  /**
   * The full address, in one line.
   */
  public function getDisplayName() :string {

    return $this->displayName;
  }

  /**
   * Returns the flat number if any.
   */
  public function getFlat() :string {

    return $this->flat;
  }

  /**
   * Returns the house name if any.
   */
  public function getHouseName() :string {

    return $this->houseName;
  }

  /**
   * Returns the organisation name if any.
   */
  public function getOrganisationName() :string {

    return $this->org;
  }

  /**
   * Getter for the OS grid reference object.
   */
  public function getOsGridRef() :OsGridRef {

    return $this->osGridRef;
  }

  /**
   * Creates an Address from an array.
   *
   * @return static
   */
  public static function createFromArray(array $data) {

    $self = parent::createFromArray($data);

    $self->uprn        = $data['uprn'] ?? '';
    $self->displayName = $data['display'] ?? '';
    $self->flat        = $data['flat'] ?? '';
    $self->houseName   = $data['houseName'] ?? '';
    $self->org         = $data['org'] ?? '';

    if (isset($data['easting']) && isset($data['northing'])) {
      $self->osGridRef = new OsGridRef((int) $data['easting'], (int) $data['northing']);
    }

    return $self;
  }

  /**
   * Appends uprn and display value to Location array.
   *
   * {@inheritdoc}
   */
  public function toArray() :array {

    $array = parent::toArray();

    $array['uprn']      = $this->getUprn();
    $array['display']   = $this->getDisplayName();
    $array['flat']      = $this->getFlat();
    $array['houseName'] = $this->getHouseName();
    $array['org']       = $this->getOrganisationName();

    if ($this->osGridRef) {
      $array['easting']  = $this->osGridRef->getEasting();
      $array['northing'] = $this->osGridRef->getNorthing();
    }

    return $array;
  }

}
