I am trying to build a program that will solve such problems as:
18 people need to meet each other during group interviews over a certain period. Four rooms are available, so there will be four interview groups at once. We need to come up with an optimal (or close to it) set of combinations so everyone can meet each other in as few sessions as possible, so the sessions can be as long as possible.

First I built two classes to represent items and groups. I haven't documented them yet, but the public methods should be pretty obvious.

Then I wrote some functional programming to create a number of items and attempt to group them.

When I first wrote this, I tried a couple different strategies, all of which were unsatisfactory. I have since simplified, restructured and rewritten this, so you'll have to look at the repo history if interested in how I failed. :P

This version employs three strategies to arrive at the optimal scenario:

1. Randomly shuffle groups each session
2. Generate a bunch (groups*items*50) of combinations and keep the best one
3. "Smart" group assignment (not finished yet)

I'm currently using the second strategy, as it seems to arrive at an optimal arrangement in a reasonable amount of time. 

Note: By my calculations, there are 192,972,780 possible combinations (not permutations) of 18 items into 4 groups. It would probably therefore be too processing intensive to generate all combinations, and then test various orders of those combinations...

For example, you can go to this URL: http://charliecorp.com/cmb/?i=%22A%20B%20C%20D%20E%20F%20G%20H%22&g=2
This is 8 items, 2 groups at a time.

    Created 8 Item objects

    Starting 800 tests

    Starting 800 tests
    New high on test 40: 1

    Scenario:
    Arrangement: (A, B, C, D), (E, F, G, H),
    Arrangement: (B, D, E, G), (A, F, C, H),
    Arrangement: (G, C, E, A), (D, F, B, H)
    Script took 0.32181000709534 seconds to work through 3 arrangements.

For our 18,4 example: http://charliecorp.com/cmb/?i=%22A%20B%20C%20D%20E%20F%20G%20H%20I%20J%20K%20L%20M%20N%20O%20P%20Q%20R%22&g=4

    Created 18 Item objects
    Starting 3600 tests
    New high on test 44: 0.39869281045752
    New high on test 470: 0.40522875816993

    Starting 3600 tests
    New high on test 0: 0.52941176470588
    New high on test 4: 0.5359477124183
    New high on test 5: 0.54901960784314
    New high on test 51: 0.55555555555556
    New high on test 86: 0.56209150326797
    New high on test 189: 0.56862745098039
    New high on test 386: 0.57516339869281
    New high on test 659: 0.58169934640523

    Starting 3600 tests
    New high on test 1: 0.67320261437909
    New high on test 5: 0.70588235294118
    New high on test 145: 0.72549019607843
    New high on test 248: 0.73202614379085
    New high on test 738: 0.73856209150327

    Starting 3600 tests
    New high on test 0: 0.80392156862745
    New high on test 2: 0.81045751633987
    New high on test 22: 0.83006535947712
    New high on test 173: 0.84313725490196
    New high on test 556: 0.86928104575163

    Starting 3600 tests
    New high on test 0: 0.90849673202614
    New high on test 20: 0.91503267973856
    New high on test 25: 0.92156862745098
    New high on test 129: 0.9281045751634
    New high on test 455: 0.93464052287582

    Starting 3600 tests
    New high on test 0: 0.96732026143791
    New high on test 154: 0.98039215686275

    Starting 3600 tests
    New high on test 2: 0.98692810457516
    New high on test 7: 0.99346405228758
    New high on test 15: 1

    Scenario:
    Arrangement: (A, B, C, D, E), (F, G, H, I, J), (K, L, M, N), (O, P, Q, R),
    Arrangement: (H, E, K, G, Q), (N, F, C, P, A), (J, L, B, R), (O, M, D, I),
    Arrangement: (H, N, O, B, I), (G, A, L, E, P), (J, Q, M, C), (D, F, R, K),
    Arrangement: (F, L, N, E, Q), (O, C, I, K, A), (R, B, G, M), (P, H, J, D),
    Arrangement: (F, O, E, K, J), (G, D, N, L, C), (P, I, Q, B), (H, R, M, A),
    Arrangement: (D, O, G, Q, A), (C, I, H, R, N), (E, K, L, B), (J, M, P, F),
    Arrangement: (Q, A, C, B, F), (L, N, I, J, H), (E, R, G, M), (O, K, P, D),
    Arrangement: (D, G, I, E, C), (J, F, N, H, A), (Q, B, L, O), (K, R, P, M)

    Script took 12.172544002533 seconds to work through 8 arrangements.

When I get some more time, I'm going to work on a "smart" assignment where items will be grouped into the group with the most items it hasn't been exposed to yet. This is similar to an earlier strategy I tried, so I'm not sure if it will work better.
