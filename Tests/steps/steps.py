from selenium.webdriver.common.by import By
from automation import *
from behave import *

#1
@given("I am situated on the calculator subpage")
def step_impl(context):
    calculate = Calculate(context.driver)
    assert calculate.calculate_exists()

@when("I configure all the parameters: {width}, {length}, {height}, {number_of_people}, {number_of_agd_devices}, {instalation_length}, {concrete_length} and click calculate button")
def step_impl(context, width, length, height, number_of_people, number_of_agd_devices, instalation_length, concrete_length):
    calculate = Calculate(context.driver)
    calculate.calculate(width, length, height, number_of_people, number_of_agd_devices, instalation_length, concrete_length)

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

@then("I proceed to summary subpage and see {expected_text}")
def step_impl(context, expected_text):
    credentials = Credentials(context.driver)
    assert credentials.credentials_text_exists(expected_text), f"Didn't find '{expected_text}' on the subpage!"
