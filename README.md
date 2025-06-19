# CQRS Library

[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.3-8892BF.svg)](https://php.net/)
[![Latest Version](https://img.shields.io/packagist/v/molibdenius/cqrs-bundle.svg?style=flat-square)](https://packagist.org/packages/molibdenius/cqrs)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

A modern PHP 8.3+ implementation of the Command Query Responsibility Segregation (CQRS) pattern, built with performance
and scalability in mind.

## ✨ Features

- 🚀 **HTTP and Queue-based Command Processing**
- 🔄 **Asynchronous Task Processing** with RoadRunner
- 📦 **Dependency Injection** with PSR-11 Container
- 🎯 **Event-Driven Architecture** using Symfony Event Dispatcher
- 🌐 **PSR-7/PSR-17 HTTP Messages** for request/response handling
- 🛠 **Built-in Retry Mechanism** for failed tasks
- 📊 **Logging** with PSR-3 compatible loggers
- 🧪 **Strict Types** and modern PHP 8.3+ features

## 📋 Requirements

- PHP 8.3 or higher
- RoadRunner
- Composer

## 🚀 Installation

```bash
composer require molibdenius/cqrs
```

## 🏗 Project Structure

```
src/
├── ActionBus/         # Command/Query bus implementation
├── Dispatcher/        # HTTP and Queue dispatchers
├── Event/             # Event classes and subscribers
├── EventLoop/         # Event loop implementations
├── Exception/         # Custom exceptions
├── Registry/          # Action and handler registry
└── Result/            # Result objects
```

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 📧 Contact

- Email: molibdenius@gmail.com
