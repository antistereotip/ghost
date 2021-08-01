#!/bin/bash
Recipient=”milutingavrilovic@gmail.com”
Subject=”Greeting”
Message=”Welcome to our site”
`mail -s $Subject $Recipient <<< $Message`
