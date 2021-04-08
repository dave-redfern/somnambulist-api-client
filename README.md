# Somnambulist API Client Library

[![GitHub Actions Build Status](https://github.com/somnambulist-tech/api-client/workflows/tests/badge.svg)](https://github.com/somnambulist-tech/api-client/actions?query=workflow%3Atests)

The ApiClient library is intended to help build client libraries for consuming JSON APIs.
The library provides abstract models for primary resource objects and related value objects.
Persistence requests are handled by ApiActions that encapsulate a change request.

Models and ValueObjects make use of [somnambulist/attribute-model](https://github.com/somnambulist-tech/attribute-model) type casting system.

The library uses Symfony HTTP Client under the hood.

## Requirements

 * PHP 8.0+
 * cURL
 * symfony/event-dispatcher
 * symfony/http-client
 * symfony/routing

## Installation

Install using composer, or checkout / pull the files from github.com.

 * composer require somnambulist/api-client

## Usage

This library provides some building blocks to help you get started with consuming RESTful
APIs. Typically this is for use with a micro-services project where you need to write
clients that will be shared amongst other projects.

Please note: this project does not make assumptions about the type of service being used.
The included libraries provide suitable defaults, but can be completely replaced by your
own implementations.

The docs are available in the docs folder with a suggested reading order as follows:

 * [upgrade notes](docs/upgrading_from_1.X_to_2.0.md)
 * [adding routes](docs/routing.md)
 * [defining API connections](docs/connections.md)
 * [expected JSON structure](docs/json_format.md)
 * [using Models and ValueObjects](docs/models.md)
 * [type casting attributes](docs/type_casting.md)
 * [model relationships](docs/model_relationships.md)
 * [storing data](docs/persistence.md)
 * [recording API responses](docs/recording.md)

## Tests

PHPUnit 9+ is used for testing. Run tests via `vendor/bin/phpunit`.

Test data was generated using faker and was randomly generated.

## Links

 * [Symfony HTTP Client](https://symfony.com/doc/current/components/http_client.html)
 * [Somnambulist Read-Models](https://github.com/somnambulist-tech/read-models)
 * [Somnambulist Attribute-Model](https://github.com/somnambulist-tech/attribute-model)
 * [Somnambulist Domain](https://github.com/somnambulist-tech/domain)
