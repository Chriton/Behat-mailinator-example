<?php

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode,
	Behat\MinkExtension\Context\MinkContext;

//
// Require 3rd-party libraries here:
//
//   require_once 'PHPUnit/Autoload.php';
//   require_once 'PHPUnit/Framework/Assert/Functions.php';
require_once 'Mailinator.php';
//

/**
 * Features context.
 */
class FeatureContext extends MinkContext
{
	public static $email;
	const FIRST_NAME = 'Doru';
	const LAST_NAME = 'Muntean';
	const PASSWORD = 'tester123';
	const MAILINATOR_TOKEN = 'place your own token here';  //Note that it will not work without your API token!
	/** To get the MAILINATOR_TOKEN you have to go to www.mailinator.com, create an
	* account and then in the Settings you will find the field 'Your API Token'. Pls use your own :)
	* Also note that with a free account you cannot make too many requests, so if you get the response code
	* 429 from the API this means 'Too Many Requests'
	*/

    /**
     * Initializes context.
     * Every scenario gets it's own context object.
     *
     * @param array $parameters context parameters (set them up through behat.yml)
     */
    public function __construct(array $parameters)
    {
        // Initialize your context here
    }

	/**
	 * @When /^I create an account$/
	 */
	public function iCreateAnAccount()
	{
		try
		{
		//go to the registration page, we use http://demo.magentocommerce.com/ as the homepage
		$this->visit('/customer/account/create/');

		//we generate a random mailinator email that starts with FIRST_NAME
		self::$email = uniqid(self::FIRST_NAME).'@mailinator.com';

		//complete the registration form
		$this->fillField('firstname',self::FIRST_NAME);
		$this->fillField('lastname',self::LAST_NAME);
		$this->fillField('email',self::$email);
		$this->fillField('password',self::PASSWORD);
		$this->fillField('confirmation',self::PASSWORD);
		$this->pressButton('Register');

		//for testing only
		echo "Email address used for registration: " . self::$email . "\n";
		}

		catch(Exception $e)
			{
			//we will fail this step if we have any exceptions
			throw new Exception("Something went wrong: " . $e->getMessage()) ;
			}
	}

	/**
	 * @Then /^I should receive a confirmation email$/
	 */
	public function iShouldReceiveAConfirmationEmail()
	{
		$mailinator = new Mailinator(self::MAILINATOR_TOKEN);

		//we can wait or not for the email to arrive in the inbox
		sleep(5);  //let's wait 5sec

		try
			{
			$data = $mailinator->fetchInbox(self::$email);

			if(count($data) > 0)  //if we have emails in the inbox
				{
				/**
				 * Note that the $data array returned by the mailinator API will have keys in the array starting with 0
				 * for ex. if we have 2 emails in the inbox then:
				 * $data[0]['id']  - is the id of the oldest email
				 * and $data[1]['id'] - is the id of the latest email
				 *
				 * the structure of the $data array returned:
				 *array(9) {
					'seconds_ago'
					'id'
					'to'
					'time'
					'subject'
					'fromfull'
					'been_read'
					'from'
					'ip'
				 	}
				 */
				//var_dump($data);
				sleep(2); //note that if we make to many requests we will get api error 429 'Too Many Requests'. This is a limitation of the free mailinator account

				//let's check if the latest email is from 'Madison Island' with the subject 'Welcome, FIRST_NAME LAST_NAME!'
				if($data[count($data)-1]['from'] == 'Madison Island' && $data[count($data)-1]['subject'] == 'Welcome, ' . self::FIRST_NAME . ' ' . self::LAST_NAME . '!')
					{
					echo "'We have received the registration email!\n";
					echo "We have an email from : " . $data[count($data)-1]['from'] . "\n";
					echo "With the subject : " . $data[count($data)-1]['subject'] . "\n";
					}
				else
					{
					throw new Exception("The registration email was not found!\n");
					}


				//we can also get the content of the email if we want
				$email_id = $data[count($data)-1]['id']; //let's get the id of the latest email
				$email_content = $mailinator->fetchMail($email_id); //then we can get an array with the content of the email
				echo "Email content:\n";
				print_r($email_content);
				}

			else  //id we do not have any emails in the inbox
				{
				throw new Exception("No emails found in the inbox.\n");
				}

			}
		catch(Exception $e)
			{
			//we will fail this step if we have any exceptions
			throw new Exception("Something went wrong: " . $e->getMessage()) ;
			}
	}
}
