<?php

declare(strict_types = 1);

namespace Drupal\localgov_forms\Geocoder\Model;

/**
 * All numeric Ordnance Survey National Grid reference.
 *
 * @see https://en.wikipedia.org/wiki/Ordnance_Survey_National_Grid#All-numeric_grid_references
 */
class OsGridRef {

  /**
   * Numeric easting.
   *
   * @var int
   */
  protected $easting;

  /**
   * Numeric northing.
   *
   * @var int
   */
  protected $northing;

  /**
   * Keeps track of the grid references.
   */
  public function __construct(int $easting, int $northing) {

    $this->easting = $easting;
    $this->northing = $northing;
  }

  /**
   * As it says on the tin.
   */
  public function getEasting() :int {

    return $this->easting;
  }

  /**
   * As it says on the tin.
   */
  public function getNorthing() :int {

    return $this->northing;
  }

}
