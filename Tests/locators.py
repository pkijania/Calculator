from selenium.webdriver.common.by import By
from automation import *
from behave import *

class Other:
    coockies = (By.ID, "sfAgreeAllButton")
    submit = (By.NAME, "cf-count")

class Size:
    width = (By.NAME, "width")
    length = (By.NAME, "length")
    height = (By.NAME, "height")

class Content:
    number_of_people = (By.NAME, "number_of_people")
    number_of_agd_devices = (By.NAME, "number_of_agd_devices")
    instalation_length = (By.NAME, "instalation_length")

class Brands:
    gree_device = (By.NAME, "gree_device")
    lg_device = (By.NAME, "lg_device")
    panasonic_device = (By.NAME, "panasonic_device")

class Layout:
    attic = (By.NAME, "attic")
    position_of_window = (By.NAME, "position_of_window")

class Instalation:
    use_trough = (By.ID, "use_trough")
    trough_length = (By.ID, "trough_length")
    use_concrete = (By.ID, "use_concrete")
    concrete_length = (By.ID, "concrete_length")
    use_bricks = (By.ID, "use_bricks")
    brick_length = (By.ID, "brick_length")

class Details:
    name = (By.NAME, "cf-name")
    email = (By.NAME, "cf-email")
    telephone = (By.NAME, "cf-telephone")
    location = (By.NAME, "cf-location")
    message = (By.NAME, "cf-message")
    send = (By.NAME, "cf-submitted")