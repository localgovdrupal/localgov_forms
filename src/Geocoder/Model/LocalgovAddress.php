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
   * Creates an Address from an array.
   *
   * @return static
   */
  public static function createFromArray(array $data) {

    $self = parent::createFromArray($data);

    $self->uprn        = $data['uprn'] ?? '';
    $self->displayName = $data['display'] ?? '';
    $self->flat        = $data['flat'] ?? '';

    return $self;
  }

  /**
   * Appends uprn and display value to Location array.
   *
   * {@inheritdoc}
   */
  public function toArray() :array {

    $array = parent::toArray();

    $array['uprn']    = $this->getUprn();
    $array['display'] = $this->getDisplayName();
    $array['flat']    = $this->getFlat();

    return $array;
  }

}
