Feature: Calculate_custom
    Test which calculates power using custom parameters

Scenario: Calculate
    Given I am situated on the calculator subpage
    When I accept cookies and configure size parameters
    And  I configure content parameters
    And I choose brands and layout parameters
    And I configure wall material and click calculate button
    Then I proceed to credentials subpage

Scenario: Credentials
    Given I am situated on the credentials subpage
    When I provide credentials and click send button
    Then I proceed to summary subpage and see expected text
