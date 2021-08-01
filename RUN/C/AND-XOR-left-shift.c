#include <stdio.h>

int main( void )
{
    unsigned int x = 3, y = 1, sum, carry;
    sum = x ^ y; // x XOR y
    carry = x & y; // x AND y
    while (carry != 0)
    {
        carry = carry << 1; // left shift the carry
        x = sum; // initialize x as sum
        y = carry; // initialize y as carry
        sum = x ^ y; // sum is calculated
        carry = x & y; /* carry is calculated, the loop condition is 
                          evaluated and the process is repeated until 
                          carry is equal to 0.
                        */
    }
    printf("%u\n", sum); // the program will print 4
    return 0;
}
