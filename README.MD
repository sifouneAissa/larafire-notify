# Larafire Notify

[![Latest Version on Packagist](https://img.shields.io/packagist/v/sifouneaissa/larafire-notify.svg?style=flat-square)](https://packagist.org/packages/sifouneaissa/larafire-notify)
[![Total Downloads](https://img.shields.io/packagist/dt/sifouneaissa/larafire-notify.svg?style=flat-square)](https://packagist.org/packages/sifouneaissa/larafire-notify)
[![License](https://img.shields.io/packagist/l/sifouneaissa/larafire-notify.svg?style=flat-square)](https://packagist.org/packages/sifouneaissa/larafire-notify)

A Laravel package for sending Firebase Cloud Messages (FCM) with topic management capabilities.

## Features

- Send notifications to single or multiple devices
- Subscribe/unsubscribe devices to topics
- Easy integration with Laravel applications
- Supports Firebase authentication
- Configurable through Laravel config files

## Configuration 
FIREBASE_CREDENTIALS=storage/app/firebase-credentials.json
APP_DOMAIN_NAME=yourdomain.com

## Firebase Setup
- Download your Firebase service account JSON file from the Firebase Console
- Store it in your project (recommended location: storage/app/)
- Update your .env:
- FIREBASE_CREDENTIALS=storage/app/your-firebase-key.json

## Installation

1. Install the package via Composer:

```bash
composer require sifouneaissa/larafire-notify
php artisan vendor:publish --provider="Sifouneaissa\LarafireNotify\LarafireNotifyServiceProvider" --tag="larafire-notify-config"

