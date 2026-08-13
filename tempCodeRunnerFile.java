import java.util.Scanner;
public class main {
    enum Coin{
        ONE,
        TWO,
        FIVE,
        TEN
    }
    public static void main(String[] args) {
        int snackprice=15;
        int total=0;
        
        Scanner sc=new Scanner(System.in);
        
       while(total<snackprice)
       {
        System.out.println("Enter coin:");
        String coin=sc.next().toUpperCase();

        Coin c=Coin.valueOf(coin);
        int value = 0;
        switch(c){
            case ONE:
            value=1;
            break;
            case TWO:
            value=2;
            break;
            case FIVE:
            value=5;
            break;
            case TEN:
            value=10;
            break;
            default :
            value=0;
        };
        total+=value;
        System.out.println("Total= " + total);
       }
       int change=total-snackprice;
       System.out.println("change=" + change);
    }
}
