public class Example {
  public static void main(String[] args){
    if(args.length != 1){
      System.err.println("First argument must be a filename");
      return;
    }
    // the first argument contains the filename of the XML file
    String filename = args[0];
    // for this example, we won't be needing it. But normally, you would open
    // and read the file.

    System.out.println("<resource><id>0</id><content>Hello from Java!</content></resource>");

  }
}
