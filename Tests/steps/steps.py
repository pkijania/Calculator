from automation import *
from behave import *

#1
@given("I am situated on the calculator subpage")
def step_impl(context):
    calculate = Calculate(context.driver)
    assert calculate.calculate_exists()

@when("I accept cookies and configure size parameters: {width}, {length}, {height}")
def step_impl(context, width, length, height):
    calculate = Calculate(context.driver)
    calculate.calculate_size(width, length, height)

@step("I configure content parameters: {number_of_people}, {number_of_agd_devices}, {instalation_length}")
def step_impl(context, number_of_people, number_of_agd_devices, instalation_length):
    calculate = Calculate(context.driver)
    calculate.calculate_content(number_of_people, number_of_agd_devices, instalation_length)

@step("I choose brands and layout parameters")
def step_impl(context):
    calculate = Calculate(context.driver)
    calculate.calculate_brands_and_layout()

@step("I configure wall material: {concrete_length} and click calculate button")
def step_impl(context, concrete_length):
    calculate = Calculate(context.driver)
    calculate.calculate_intalation(concrete_length)

@then("I proceed to credentials subpage")
def step_impl(context):
    credentials = Credentials(context.driver)
    assert credentials.credentials_exists()

#2
@given("I am situated on the credentials subpage")
def step_impl(context):
    credentials = Credentials(context.driver)
    assert credentials.credentials_exists()

@when("I provide my credentials: {name}, {email}, {telephone}, {location} and click send button")
def step_impl(context, name, email, telephone, location):
    credentials = Credentials(context.driver)
    credentials.credentials(name, email, telephone, location)

@then("I proceed to summary subpage and see: {expected_text}")
def step_impl(context, expected_text):
    credentials = Credentials(context.driver)
    assert credentials.credentials_text_exists(expected_text), f"Didn't find '{expected_text}' on the subpage!"
