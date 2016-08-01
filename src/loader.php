<?php

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */

/**
 * @return \Milky\Framework
 */
function fw( $basePath = null )
{
	if ( Milky\Framework::isRunning() )
		return Milky\Framework::fw();

	$fw = new Milky\Framework( $basePath );

	// Register class and manager implementations here

	return $fw;
}