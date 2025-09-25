<?php
/* Copyright (C) 2025 THERSANE
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace collabtrack;
// Class used for ajax responses in Dolibarr
class JsonResponse
{

	/**
	 * the call status to determine if success or fail
	 *
	 * @var int $result
	 */
	public $result = 0;

	/**
	 * data to return to call can be all type you want
	 *
	 * @var mixed
	 */
	public $data;

	/**
	 * debug data
	 *
	 * @var mixed
	 */
	public $debug;

	/**
	 * returned message used usually as set event message
	 *
	 * @var string $msg
	 */
	public $msg = '';

	/**
	 * the current newToken
	 *
	 * @var mixed|string
	 */
	public $newToken = '';

	public function __construct()
	{
		$this->newToken = newToken();
	}

	/**
	 * return json encoded of object
	 *
	 * @return string JSON
	 */
	public function getResponse()
	{
		$jsonResponse = new \stdClass();
		$jsonResponse->result = $this->result;
		$jsonResponse->msg = $this->msg;
		$jsonResponse->newToken = $this->newToken;
		$jsonResponse->data = $this->data;
		$jsonResponse->debug = $this->debug;

		return json_encode($jsonResponse, JSON_PRETTY_PRINT);
	}
}
