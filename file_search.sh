#!/bin/bash

find /home/careerph/public_html/uploads/applicants/ -iname "*$1*" >> "/home/careerph/public_html/$2"
