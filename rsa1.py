from math import gcd

def is_prime(n: int) -> bool:
    """Check whether n is a prime number (good for small/medium numbers in exercises)."""
    if n < 2:
        return False
    if n % 2 == 0:
        return n == 2
    i = 3
    while i * i <= n:
        if n % i == 0:
            return False
        i += 2
    return True

def read_prime(prompt: str) -> int:
    """Read a prime number from the user; reject invalid/non-prime inputs."""
    while True:
        try:
            x = int(input(prompt))
            if is_prime(x):
                return x
            print(" This is not a prime number. Please try again.")
        except ValueError:
            print(" Invalid input. Please enter an integer number only.")

def read_e(phi: int) -> int:
    """Read e from the user and validate RSA conditions: 1 < e < phi and gcd(e, phi) = 1."""
    while True:
        try:
            e = int(input(f"Choose public exponent e (1 < e < {phi} and gcd(e, phi)=1): "))
            if 1 < e < phi and gcd(e, phi) == 1:
                return e
            print(" Invalid e. It must satisfy: 1 < e < phi and gcd(e, phi) = 1. Try again.")
        except ValueError:
            print(" Invalid input. Please enter an integer number only.")

# Extended Euclidean Algorithm
def extended_gcd(a, b):
    # Returns (g, x, y) such that a*x + b*y = g = gcd(a, b)
    if b == 0:
        return a, 1, 0
    g, x1, y1 = extended_gcd(b, a % b)
    x = y1
    y = x1 - (a // b) * y1
    return g, x, y

def mod_inverse(e, phi):
    """Compute d = e^{-1} (mod phi) using Extended Euclidean Algorithm."""
    g, x, _ = extended_gcd(e, phi)
    if g != 1:
        raise ValueError("No modular inverse exists because gcd(e, phi) != 1.")
    return x % phi

# ===== RSA Key Generation =====
p = read_prime("Enter prime number p: ")
q = read_prime("Enter prime number q: ")

while q == p:
    print(" q must be different from p. Please enter a different prime number.")
    q = read_prime("Enter prime number q: ")

n = p * q
phi = (p - 1) * (q - 1)

print("\nComputed values:")
print("n      =", n)
print("phi(n) =", phi)

e = read_e(phi)          # <-- user chooses e
d = mod_inverse(e, phi)  # compute d automatically

print("\n===== RSA Keys =====")
print(f"Public Key  -> (n, e) = ({n}, {e})")
print(f"Private Key -> (n, d) = ({n}, {d})")