<?php
/*
Plugin Name: FacetWP - Radio
Plugin URI: https://facetwp.com/
Description: Radio facet
Version: 1.0.0
Author: Matt Gibbs
Author URI: https://facetwp.com/
GitHub Plugin URI: https://github.com/mgibbs189/facetwp-radio
GitHub Branch: 1.0.0

Copyright 2014 Matt Gibbs

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/>.
*/

// exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;


/**
 * WordPress init hook
 */
add_action( 'init' , 'fwpradio_init' );


/**
 * Intialize facet registration and any assets
 */
function fwpradio_init() {
    add_filter( 'facetwp_facet_types', 'fwpradio_facet_types' );
}


/**
 * Register the facet type
 */
function fwpradio_facet_types( $facet_types ) {
    include( dirname( __FILE__ ) . '/facetwp-radio.php' );
    $facet_types['radio'] = new FacetWP_Facet_Radio();
    return $facet_types;
}