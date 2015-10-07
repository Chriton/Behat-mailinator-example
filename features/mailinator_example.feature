Feature:
	As a software tester
	I want to check if a confirmation email is send
	In order to make testing easier


	@javascript
	Scenario: Create account and check confirmation email
		Given I am on homepage
		When I create an account
		Then I should see "My Dashboard"
		And I should receive a confirmation email
