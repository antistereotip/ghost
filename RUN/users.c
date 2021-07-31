#include<stdio.h>
#include<sys/utsname.h>
#include<utmp.h>

int main(void)
{
  struct utmp *n;
  setutent();
  n=getutent();

  while(n) {
    if(n->ut_type==USER_PROCESS) {
      printf("%9s%12s (%s)\n", n->ut_user, n->ut_line, n->ut_host);
    }
    n=getutent();
  }
  return 0;
}
