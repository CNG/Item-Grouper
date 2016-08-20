Item Grouper efficiently arranges things in a series of groups. More specifically, it is a program meant to determine an efficient way to repeatedly arrange a set of items into a variable number of groups in as few rounds as possible to allow each item to be exposed to each other item at least once.

## Impetus

I wrote it to help my mom with some tedium at work where she would have to solve such problems as:

18 people need to meet each other during group interviews over a certain period. Four rooms are available, so there will be four interview groups at once. We need to come up with an optimal (or close to it) set of combinations so everyone can meet each other in as few sessions as possible, so the sessions can be as long as possible.

## Approach

First I built two classes to represent items and groups. I haven't documented them yet, but the public methods try to be obvious. Then I wrote some functional programming to create a number of items and attempt to group them.

When I first wrote this, I tried a couple different strategies, all of which were unsatisfactory. I have since simplified, restructured and rewritten this, so you'll have to look at the repo history if interested in how I failed. :P

This version employs three strategies to arrive at the optimal scenario:

1. Randomly shuffle groups each session
2. Generate a bunch (# groups × # items × 50) of combinations and keep the best one
3. "Smart" group assignment (not finished yet)

I'm currently using the second strategy, as it seems to arrive at an optimal arrangement in a reasonable amount of time. 

*Note: By my calculations, there are 192,972,780 possible combinations (not permutations) of 18 items into 4 groups. It would probably therefore be too processing intensive to generate all combinations, and then test various orders of those combinations...*

## Results

For example, go to the URL [https://votecharlie.com/blog/2013/04/item-grouper.html?i=A%0D%0AB%0D%0AC%0D%0AD%0D%0AE%0D%0AF%0D%0AG%0D%0AH&g=2](https://votecharlie.com/blog/2013/04/item-grouper.html?i=A%0D%0AB%0D%0AC%0D%0AD%0D%0AE%0D%0AF%0D%0AG%0D%0AH&g=2) for 8 items, 2 groups at a time.

> **Results:**  
> **Round 1:**  
> (A, B, C, D), (E, F, G, H)  
> **Round 2:**  
> (H, B, F, D), (C, A, E, G)  
> **Round 3:**  
> (G, D, B, E), (C, F, H, A)
> 
> Script took 0.087337970733643 seconds to work through 3 arrangements.

For our 18,4 example: [https://votecharlie.com/blog/2013/04/item-grouper.html?i=A%0D%0AB%0D%0AC%0D%0AD%0D%0AE%0D%0AF%0D%0AG%0D%0AH%0D%0AI%0D%0AJ%0D%0AK%0D%0AL%0D%0AM%0D%0AN%0D%0AO%0D%0AP%0D%0AQ%0D%0AR&g=4](https://votecharlie.com/blog/2013/04/item-grouper.html?i=A%0D%0AB%0D%0AC%0D%0AD%0D%0AE%0D%0AF%0D%0AG%0D%0AH%0D%0AI%0D%0AJ%0D%0AK%0D%0AL%0D%0AM%0D%0AN%0D%0AO%0D%0AP%0D%0AQ%0D%0AR&g=4)

> **Results:**  
> **Round 1:**  
> (A, B, C, D, E), (F, G, H, I, J), (K, L, M, N), (O, P, Q, R)  
> **Round 2:**  
> (J, I, Q, A, K), (C, M, H, B, O), (E, G, P, L), (F, R, D, N)  
> **Round 3:**  
> (I, B, R, J, C), (M, L, Q, F, E), (A, G, O, N), (K, H, P, D)  
> **Round 4:**  
> (H, R, Q, E, G), (P, N, J, A, M), (D, O, I, L), (C, K, F, B)  
> **Round 5:**  
> (I, A, P, F, R), (E, G, K, J, O), (Q, C, B, N), (H, L, M, D)  
> **Round 6:**  
> (B, K, L, P, R), (F, M, C, A, G), (O, Q, D, J), (E, I, H, N)  
> **Round 7:**  
> (E, J, H, A, L), (M, R, K, I, P), (N, O, Q, F), (D, C, G, B)  
> **Round 8:**  
> (L, E, O, C, P), (J, R, F, N, M), (B, G, Q, K), (I, D, A, H)
> 
> Script took 3.3203279972076 seconds to work through 8 arrangements.

When I get some more time, I'm going to work on a "smart" assignment where items will be grouped into the group with the most items it hasn't been exposed to yet. This is similar to an earlier strategy I tried, so I'm not sure if it will work better.
