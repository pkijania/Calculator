Feature: Calculate_custom
    Test which calculates power using custom parameters

Scenario: Calculate
    Given I am situated on the calculator subpage
    When I accept cookies and configure size parameters: 5, 5, 2
    And  I configure content parameters: 2, 3, 5
    And I choose brands and layout parameters
    And I configure wall material: 4 and click calculate button
    Then I proceed to credentials subpage

Scenario: Credentials
    Given I am situated on the credentials subpage
    When I provide my credentials: John Smith, 3lastik@gmail.com, 123 123 123, Los Angeles and click send button
    Then I proceed to summary subpage and see Szczegóły dotyczące wybranych klimatyzatorów:
