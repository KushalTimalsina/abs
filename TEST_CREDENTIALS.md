# Test Login Credentials

## Superadmin Account
- **URL**: `http://localhost:8000/superadmin/login`
- **Email**: `superadmin@abs.com`
- **Password**: `password`
- **Access**: Full system administration, manage all organizations, subscription plans, and payments

---

## Admin Account (Organization Admin)
- **URL**: `http://localhost:8000/login`
- **Email**: `admin@abs.com`
- **Password**: `password`
- **Organization**: Test Organization
- **Access**: Manage organization settings, services, bookings, team members, and view analytics

---

## Staff Account (Team Member)
- **URL**: `http://localhost:8000/login`
- **Email**: `staff@abs.com`
- **Password**: `password`
- **Organization**: Test Organization
- **Access**: View and manage assigned bookings, update booking status

---

## Customer Account
- **URL**: `http://localhost:8000/login`
- **Email**: `customer@abs.com`
- **Password**: `password`
- **Access**: Book appointments, view booking history, manage profile

---

## Notes
- All passwords are set to `password` for testing purposes
- **⚠️ IMPORTANT**: Change these credentials in production!
- All accounts have verified email addresses
- Admin and Staff are attached to "Test Organization"
- The organization has an active subscription

## Quick Login Links (when server is running)
- [Superadmin Login](http://localhost:8000/superadmin/login)
- [Regular Login](http://localhost:8000/login)
- [Register New Account](http://localhost:8000/register)
- [Register Organization](http://localhost:8000/register/organization)
