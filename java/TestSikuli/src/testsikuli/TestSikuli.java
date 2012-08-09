/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
package testsikuli;

import java.util.List;
import java.util.ArrayList;
import org.sikuli.script.Finder;
import org.apache.log4j.Logger;
import org.apache.log4j.BasicConfigurator;

/**
 *
 * @author guanjianjun
 */
public class TestSikuli {
    static Logger logger = Logger.getLogger(TestSikuli.class);
    /**
     * @param args the command line arguments
     */
    public static void main(String[] args) {
        // TODO code application logic here
        BasicConfigurator.configure();
        try
        {
            List<String> inputList = new ArrayList<String>();
            
            //sina
            inputList.add("d:\\sina\\0.jpg");
            inputList.add("d:\\sina\\10.jpg");
            inputList.add("d:\\sina\\30.jpg");
            inputList.add("d:\\sina\\60.jpg");
            inputList.add("d:\\sina\\90.jpg");
            inputList.add("d:\\sina\\100.jpg");
            inputList.add("d:\\sina\\error1.png");
            String expect = "d:\\sina\\expect.png";
            double miniSimular = 0.9999;
            
            for (int i=0; i<inputList.size(); ++i)
            {                
                logger.info("input file: " + inputList.get(i));
                Finder finder = new Finder(inputList.get(i));
            
                finder.find(expect, miniSimular);
                int k = 0;
                while (finder.hasNext())
                {
                    ++k;
                    logger.info(String.format("found: %d", k));
                    finder.next();                    
                }
            }
        }
        catch (Exception ex)
        {
            
        }
    }
}
