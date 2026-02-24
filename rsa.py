import base64

# -----------------------
# Basic Math Functions
# -----------------------

def gcd(a, b):
    while b:
        a, b = b, a % b
    return a

def extended_gcd(a, b):
    if a == 0:
        return b, 0, 1
    gcd_val, x1, y1 = extended_gcd(b % a, a)
    x = y1 - (b // a) * x1
    y = x1
    return gcd_val, x, y

def mod_inverse(e, phi):
    gcd_val, x, y = extended_gcd(e, phi)
    if gcd_val != 1:
        return None
    return x % phi


# -----------------------
# Key Generation
# -----------------------

print("                                           === RSA Key Generation ===")

p = int(input("Enter prime number p: "))
q = int(input("Enter prime number q: "))

n = p * q
phi = (p - 1) * (q - 1)

print("n =", n)
print("phi(n) =", phi)

e = int(input("Choose public exponent e: "))

if e <= 1 or e >= phi or gcd(e, phi) != 1:
    print("Invalid e value")
    exit()

d = mod_inverse(e, phi)

print("Public Key (n, e):", (n, e))
print("Private Key (n, d):", (n, d))