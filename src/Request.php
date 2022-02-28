<?php

namespace App\Core;

use App\Core\App;
use App\Core\Filesystem\Filesystem;


class Request
{
	/**
	 * Fetch the request URI.
	 *
	 * @return string
	 */
	public static function uri()
	{
		$base_uri = (App::get('base_url') != "")
			? str_replace(App::get('base_url'), "", $_SERVER['REQUEST_URI'])
			: $_SERVER['REQUEST_URI'];

		return parse_url($base_uri, PHP_URL_PATH);
	}

	/**
	 * Fetch the request method.
	 *
	 * @return string
	 */
	public static function method()
	{
		return $_SERVER['REQUEST_METHOD'];
	}

	/**
	 * Validates the POST method inputs
	 * check if the input has validation set.
	 * 
	 */
	public static function validator($datas = [])
	{
		$errorList = [];
		foreach ($datas as $key => $data) {
			foreach ($data as $types) {
				$type = explode(':', $types);

				switch ($type[0]) {
					case 'required':
						if ($_REQUEST[$key] == "") {
							$errorList[$key] = "{$key} is {$type[0]} but has no value.";
						}

						break;

					case 'min':
						if (strlen($_REQUEST[$key]) < $type[1]) {
							$errorList[$key] = "{$key} is less than {$type[1]} character/s.";
						}

						break;

					case 'max':
						if (strlen($_REQUEST[$key]) > $type[1]) {
							$errorList[$key] = "{$key} is greater than {$type[1]} character/s.";
						}

						break;

					case 'email':
						if (strpos('@', $_REQUEST[$key]) !== false) {
							$errorList[$key] = "{$key} is not a valid email address.";
						}

						break;

					case 'unique':

						$param = explode(",", $type[1]);
						$table = $param[0];
						if ($table != "") {

							$column = (!empty($param[1])) ? $param[1] : '';
							$except = (!empty($param[2])) ? $param[2] : '';
							$idColumn = (!empty($param[3])) ? $param[3] : '';

							// if unique:{table}
							if (empty($column) && empty($except) && empty($idColumn)) {
								$newColumn = $key;
								$query = "`{$newColumn}` = '$_REQUEST[$key]'";
							}

							// if unique:{table},{column}
							if (!empty($column) && empty($except) && empty($idColumn)) {
								$newColumn = $column;
								$query = "`{$newColumn}` = '$_REQUEST[$key]'";
							}

							// if unique:{table},{exept},{id}
							if (empty($idColumn) && !empty($column) && !empty($except)) {

								$newExcept = $column;
								$newIdColumn = $except;

								$query = "`{$key}` = '$_REQUEST[$key]' AND `{$newExcept}` != '{$newIdColumn}'";
							}

							// if unique:{table},{column},{exept},{id}
							if (!empty($column) && !empty($except) && !empty($idColumn)) {
								$query = "`{$column}` = '$_REQUEST[$key]' AND `{$except}` != '{$idColumn}'";
							}

							$response = DB()->select("count(*) as '{$table}_count'", $param[0], $query)->get();

							if ($response["{$table}_count"] > 0) {
								$errorList[$key] = "{$key} already exist in database.";
							}
						}

						break;

					case 'boolean':

						$bool = ['true', 'false', '1', '0'];
						if (!in_array($_REQUEST[$key], $bool)) {
							$errorList[$key] = "{$key} is not a boolean.";
						}

						break;

					case 'numeric':

						if (!is_numeric($_REQUEST[$key])) {
							$errorList[$key] = "{$key} is not a number.";
						}

						break;

					default:
						break;
				}
			}
		}

		return $errorList;
	}

	/**
	 * Validates the POST method inputs
	 * 
	 */
	public static function validate($uri = '', $datas = [])
	{
		$post_data['validationError'] = [];
		$errorList = static::validator($datas);
		if (!empty($errorList)) {
			$post_data['validationError'] = $errorList;

			if ($uri != '') {
				$_SESSION["RESPONSE_MSG"] = $errorList;
				redirect($uri);
			}
		}

		foreach ($_REQUEST as $key => $value) {
			$post_data[$key] = sanitizeString($value);

			if ($key != 'password') {
				$setOldInput[$key] = sanitizeString($value);
			}
		}

		static::storeValidatedToSession($setOldInput);

		if (isset($_REQUEST['csrf_token'])) {
			static::verifyCsrfToken($_REQUEST['csrf_token']);
		}

		if (!empty($_SERVER['HTTP_X_CSRF_TOKEN'])) {
			static::verifyCsrfToken($_REQUEST['csrf_token']);
		}

		return $post_data;
	}

	/**
	 * store validated request to session
	 * 
	 */
	private static function storeValidatedToSession($validatedRequest)
	{
		static::invalidateOld();
		$_SESSION['OLD'] = $validatedRequest;
	}

	/**
	 * get the old input values
	 * 
	 */
	public static function old($inputName)
	{
		return (!empty($inputName) && isset($_SESSION['OLD']) && array_key_exists($inputName, $_SESSION['OLD']))
			? $_SESSION['OLD'][$inputName]
			: '';
	}

	/**
	 * generate a token
	 * 
	 */
	public static function token($length)
	{
		return md5(bin2hex(randChar($length)));
	}

	/**
	 * will send an email containing the password reset link
	 * 
	 */
	public static function passwordResetLink($request)
	{
		$isEmailExist = DB()->select("*", "users", "email = '" . $request['email'] . "'")->get();

		if (!$isEmailExist) {
			redirect('/forgot/password', ["message" => 'E-mail not found in the server.']);
		} else {

			$token = Request::token(10);

			$subject = "Sprnva password reset link";
			$emailTemplate = file_get_contents('vendor/sprnva/framework/src/Email/stubs/email.stubs');

			$app_name = ["{{app_name}}", "{{username}}", "{{link_token}}", "{{year}}"];
			$values = [
				App::get('config')['app']['name'],
				$isEmailExist['fullname'],
				$_SERVER['SERVER_NAME'] . "/" . App::get('config')['app']['base_url'] . "/reset/password/" . $token,
				date('Y')
			];
			$body_content = str_replace($app_name, $values, $emailTemplate);

			$body = $body_content;

			$isSent = sendMail($subject, $body, $request['email']);

			if ($isSent[1] == "success") {

				$insertData = [
					'email' => $request['email'],
					'token' => $token,
					'created_at' => date("Y-m-d H:i:s")
				];

				$hasResetPending = DB()->select("email", "password_resets", "email = '" . $request['email'] . "'")->get();

				if (!empty($hasResetPending['email'])) {
					DB()->update('password_resets', $insertData, "email = '" . $request['email'] . "'");
				} else {
					DB()->insert('password_resets', $insertData);
				}
			}

			redirect('/forgot/password', $isSent['message']);
		}
	}

	/**
	 * generates a csrf token
	 * 
	 */
	public static function csrf_token()
	{
		$token = "";

		if (!isset($_SESSION["_sprnva_token_"])) {
			$_SESSION["_sprnva_token_"] = Request::token(10);
		} else {
			$token = $_SESSION["_sprnva_token_"];
		}

		return $token;
	}

	/**
	 * generates a secret csrf token
	 * for form submit
	 * 
	 */
	public static function csrf()
	{
		return bcrypt(static::csrf_token());
	}

	/**
	 * verifies csrf token
	 * match secret token vs users token
	 * 
	 */
	public static function verifyCsrfToken($request)
	{
		if (!checkHash($_SESSION["_sprnva_token_"], $request)) {
			throwException('419 | Page expired.');
		}
	}

	/**
	 * renew the csrf token
	 * 
	 */
	public static function renewCsrfToken()
	{
		$_SESSION["_sprnva_token_"] = Request::token(10);
	}

	/**
	 * unset old datas
	 * 
	 */
	public static function invalidateOld()
	{
		if (isset($_SESSION['OLD'])) {
			unset($_SESSION['OLD']);
		}
	}
}
