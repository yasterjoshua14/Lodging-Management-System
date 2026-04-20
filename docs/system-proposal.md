# Lodging Management System Proposal

## Project Overview
The Lodging Management System is a web-based application built with CodeIgniter 4 using the MVC pattern. It helps a lodging business organize room records, tenant information, and bookings in one place while providing secure user authentication for staff.

## Problem Statement
Manual lodging records are difficult to maintain because room availability, tenant data, and booking schedules are often stored in separate notebooks or spreadsheets. This increases the risk of double-booking, incomplete guest records, and slower front-desk operations.

## Proposed Solution
The system centralizes lodging operations into four connected modules:

- Authentication for account registration, login, and logout
- Room management for room type, capacity, nightly price, and status
- Tenant management for identity and contact records
- Booking management for assigning tenants to rooms and tracking stay dates

## Objectives
- Provide secure access through login and registration
- Reduce manual errors in room and booking tracking
- Maintain a searchable record of tenants
- Give staff a quick dashboard summary for daily operations

## Scope for Midterm
- User registration and login
- Dashboard with summary counts
- CRUD for rooms
- CRUD for tenants
- CRUD for bookings
- Booking overlap validation to avoid duplicate active stays for one room

## Intended Users
- Front desk staff
- Lodging manager
- Property owner or supervisor

## Technology Stack
- Framework: CodeIgniter 4
- Language: PHP 8.2
- Architecture: MVC
- Database: MySQL / MariaDB
- Frontend: HTML, CSS, server-rendered views

## Expected Benefits
- Faster room assignment and booking lookup
- Cleaner guest records
- Better visibility of occupancy and available rooms
- Easier midterm demonstration of working CRUD and authentication
