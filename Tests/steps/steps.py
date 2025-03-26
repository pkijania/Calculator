from automation import *
from behave import *

## Feature: Calculate_custom

#1 Scenario: Calculate
@given("I am situated on the calculator subpage")
def step_impl(context):
    calculate = Calculate(context.driver)
    assert calculate.calculate_exists()

@when("I accept cookies and configure size parameters")
def step_impl(context):
    calculate = Calculate(context.driver)
    calculate.calculate_size()

@step("I configure content parameters")
def step_impl(context):
    calculate = Calculate(context.driver)
    calculate.calculate_content()

@step("I choose brands and layout parameters")
def step_impl(context):
    calculate = Calculate(context.driver)
    calculate.calculate_brands_and_layout()

@step("I configure wall material and click calculate button")
def step_impl(context):
    calculate = Calculate(context.driver)
    calculate.calculate_intalation()

@then("I proceed to credentials subpage")
def step_impl(context):
    credentials = Credentials(context.driver)
    assert credentials.credentials_exists()

#2 Scenario: Credentials
@given("I am situated on the credentials subpage")
def step_impl(context):
    credentials = Credentials(context.driver)
    assert credentials.credentials_exists()

@when("I provide credentials and click send button")
def step_impl(context):
    credentials = Credentials(context.driver)
    credentials.credentials()

@then("I proceed to summary subpage and see expected text")
def step_impl(context):
    credentials = Credentials(context.driver)
    assert credentials.credentials_text_exists()
