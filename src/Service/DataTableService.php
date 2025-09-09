<?php
namespace App\Service;

use Symfony\Component\Routing\RouterInterface;
 
class DataTableService {

    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }
 
    public function getData($request, $repository, $joins = [], $furtherConditions=null): string {
         
        // Get the parameters from the Ajax Call
        if ($request->getMethod() == 'POST') {
            $parameters = $request->request->all();
            $draw = $parameters['draw'];
            $start = $parameters['start'];
            $length = $parameters['length'];
            $search = $parameters['search'];
            $columns = $parameters['columns'];

            // Check if the "order" parameter exists
            $orders = isset($parameters['order']) ? $parameters['order'] : [];

            $searchBy = $parameters['searchBy'] ?? []; // Array of custom search mappings
            $getters = $parameters['getters'] ?? []; // Array of getter methods
            $buttons = $parameters['buttons'] ?? []; // Array of paths for actions
        }
        else
            die;
        
        // Handle ordering only if "order" is not empty
        if (!empty($orders)) {
            //Order the Entries for the table
            foreach ($orders as $key => $order) {
                $orders[$key]['name'] = $columns[$order['column']]['name'];
            }
        }
 
        // Get results from the Repository
        $results = $repository->getTableData($start, $length, $orders, $search, $columns, $joins, $furtherConditions, $searchBy);
        $objects = $results["results"];
         
        // Get total number of objects
        $total_objects_count =  $repository->countObjects();
         
        // Get total number of results
        $selected_objects_count = count($objects);
         
        // Get total number of filtered data
        $filtered_objects_count = $results["countResult"];
         
        // Construct response
        $response = [
            "draw" => (int) $draw,
            "recordsTotal" => $total_objects_count,
            "recordsFiltered" => $filtered_objects_count,
            "data" => []
        ];
        
        // If you want to custom the rows, edit here
        foreach ($objects as $object) {
            $row = [];
        
            // Dynamically call getter methods to build the row
            foreach ($getters as $getter) {
                if (method_exists($object, $getter)) {
                    $value = call_user_func([$object, $getter]);

                    // Customize values for specific methods if needed
                    if (is_string($value) && strlen($value) > 500) {
                        $value = sprintf('<small>%s</small>', $value);
                    } elseif ($value instanceof \DateTime) {
                        $value = $value->format('d/m/Y');
                    } elseif (is_array($value)) {
                        //display as ul li list
                        $text = '<ul>';
                        foreach ($value as $item) {
                            $text .= '<li>' . htmlspecialchars($item, ENT_QUOTES, 'UTF-8') . '</li>';
                        }
                        $text .= '</ul>';
                        $value = $text;
                    } elseif (is_object($value)) {
                        $value = method_exists($value, '__toString') ? (string)$value : get_class($value);
                    }

                    $row[] = $value;
                } else {
                    $row[] = '-'; // Default value if method does not exist
                }
            }

            // Dynamically generate action buttons
            if (!empty($buttons)) {
                $buttonsHtml = '';
                foreach ($parameters['buttons'] as $button) {
                    $path = $button['path'] ?? null;
                    $class = $button['class'] ?? 'btn btn-sm btn-primary';
                    $label = $button['label'] ?? ucfirst(str_replace('_', ' ', basename($path)));
                    $icon = $button['icon'] ?? '';
                    $target = $button['target'] ?? '';
                    $conditionalMethod = $button['conditional'] ?? 'getId';
                    $conditional = call_user_func([$object, $conditionalMethod]);
                    $urlParams = $button['params'] ?? ['id' => 'getId'];
        
                    if ($path && $conditional) {
                        $params = [];
                        // Dynamically build the URL parameters
                        foreach ($urlParams as $key => $getter) {
                            if (method_exists($object, $getter)) {
                                $params[$key] = $object->$getter();
                            }
                        }

                        if ($icon) {
                            $icon = '<i class="material-icons" style="font-size: inherit; vertical-align: middle;">'.$icon.'</i>';
                        }

                        // Generate the URL with the dynamically built parameters
                        $url = $this->router->generate($path, $params);
                        
                        $buttonsHtml .= sprintf(
                            '<a href="%s" class="%s" target="%s">%s %s</a> ',
                            $url,
                            htmlspecialchars($class, ENT_QUOTES, 'UTF-8'),
                            htmlspecialchars($target, ENT_QUOTES, 'UTF-8'),
                            htmlspecialchars($label, ENT_QUOTES, 'UTF-8'),
                            $icon,
                        );
                    }
                }

                $row[] = $buttonsHtml;
            }
            
            $response["data"][] = $row;
        }
        
        return json_encode($response);
        
    }
         
    public function countObjectsInTable($countQuery, $table) {
        // Use DISTINCT to count only unique entities from the main table
        // This prevents counting joined rows as separate entities
        return $countQuery->select("COUNT(DISTINCT $table.id)");
    }
 
    public function setLength($countQuery, $length) {
        $countResult = $countQuery->getQuery()->getSingleScalarResult();
        if ($length == -1) {
            $length = $countResult;
        }
        return $countResult;
    }
 
    public function addJoins($query, $countQuery, $joins) {
        foreach ($joins as $join) {
            $table = $join['parentEntity'];
            $relation = $join['relation'];
            $alias = $join['alias'];
    
            // Build the join, using the table and relation parameters
            $query->leftJoin("$table.$relation", $alias);
            $countQuery->leftJoin("$table.$relation", $alias);
    
            // Add to SELECT clause for count query only (not main query to avoid duplicates)
            $countQuery->addSelect($alias);
        }
        
        // Ensure the main query returns distinct results to avoid duplicates from joins
        $query->distinct(true);
    }

    public function addLimits($query, $start, $length) {
        return $query->setFirstResult($start)->setMaxResults($length);
    }
    
    public function addConditions($query, $countQuery, $conditions) {
        if ($conditions != null) {
            // Add condition
            $query->where($conditions);
            $countQuery->where($conditions);
        }
    }
 
    public function performSearch($query, $countQuery, $table, $columns, $search, $searchBy = []) {
        $searchItem = trim($search['value'] ?? ''); // Get the search term
    
        if (empty($searchItem)) {
            return; // Skip if there's no search term
        }
    
        // Split the search term into words
        $searchItems = explode(' ', $searchItem);
        $searchGroups = [];
        $searchParameters = [];
    
        // Loop through the search items and create AND conditions
        foreach ($searchItems as $index => $searchItem) {
            $searchConditions = [];
    
            // Generic column-wise search fallback
            foreach ($columns as $column) {
                if ($column['searchable'] === "true" && !empty($column['name'])) {
                    if (array_key_exists($column['name'], $searchBy)) {
                        $name = $column['name'];
                        $config = $searchBy[$name];
                        $this->applyAdvancedSearch($name, $config, $index, $searchItem, $searchConditions, $searchParameters);
                    } else {
                        $this->applyDefaultSearch($table, $column, $index, $searchItem, $searchConditions, $searchParameters);

                    }
                }
            }
    
            if (!empty($searchConditions)) {
                $searchGroups[] = '(' . implode(' OR ', $searchConditions) . ')';
            }
        }
    
        // Apply AND conditions to queries
        if (!empty($searchGroups)) {
            $searchQuery = implode(' AND ', $searchGroups);
            $query->andWhere($searchQuery);
            $countQuery->andWhere($searchQuery);
    
            foreach ($searchParameters as $param => $value) {
                $query->setParameter($param, $value);
                $countQuery->setParameter($param, $value);
            }
        }
    }

    private function applyDefaultSearch($table, $column, $index, $searchItem, &$searchConditions, &$searchParameters) 
    {
        $columnName = $table . '.' . $column['name'];
        $paramKey = "searchTerm_{$index}";
        $searchConditions[] = "$columnName LIKE :$paramKey";
        $searchParameters[$paramKey] = "%$searchItem%";
    }

    private function applyAdvancedSearch($columnName, $config, $index, $searchItem, &$searchConditions, &$searchParameters)
    {
        if (!isset($config['columns']) || !is_array($config['columns'])) {
            return; // Skip invalid configs
        }

        if (!isset($config['pattern'])) {
            foreach ($config['columns'] as $column) {
                $paramKey = "{$columnName}_{$index}";
                $searchConditions[] = "$column LIKE :$paramKey";
                $searchParameters[$paramKey] = "%$searchItem%";
            }
        } else {
            $terms = $this->extractWithRegex($searchItem, $config['pattern']);

            if (!empty($terms)) {  // Ensure we got both parts
        
                // Build the CONCAT dynamically using the columns in config
                $concatExpression = 'CONCAT(' . implode(', ', $config['columns']) . ')';

                // Join terms together (as per regex pattern)
                $searchTerm = implode('', $terms);  // This will combine all terms like "IIC1103"

                // Match concatenated value
                $paramKey = "{$columnName}_{$index}";
                $searchConditions[] = "$concatExpression LIKE :$paramKey";
                $searchParameters[$paramKey] = "%$searchTerm%";
            } else {
                foreach ($config['columns'] as $column) {
                    $paramKey = "{$columnName}_{$index}";
                    $searchConditions[] = "$column LIKE :$paramKey";
                    $searchParameters[$paramKey] = "%$searchItem%";
                }
            }
        }
    }
     
    public function performOrdering($query, $orders, $table, $orderByConfig = []) {
        // Clear all existing order clauses
        $query->resetDQLPart('orderBy');
        
        foreach ($orders as $order) {
            if (!empty($order['name'])) {
                $orderDirection = strtoupper($order['dir']);
    
                // Validate the direction to be either ASC or DESC
                if (in_array($orderDirection, ['ASC', 'DESC'])) {
                    // Check if the order column has a custom configuration
                    if (array_key_exists($order['name'], $orderByConfig)) {
                        $config = $orderByConfig[$order['name']];
                        if (isset($config['columns']) && is_array($config['columns'])) {

                            if (count($config['columns']) > 1) {
                                // Concatenate the columns for ordering
                                $concatColumns = implode(', ', $config['columns']);
                                $orderColumn = "CONCAT($concatColumns)";
                            } else {
                                $orderColumn = $config['columns'][0];
                            }
                            $query->addOrderBy($orderColumn, $orderDirection);
                        }
                    } else {
                        // Default ordering
                        $orderColumn = "{$table}.{$order['name']}";
                        $query->addOrderBy($orderColumn, $orderDirection);
                    }
                }
            }
        }
    }

    function extractWithRegex($input, $pattern) {
        if (preg_match($pattern, $input, $matches)) {
            array_shift($matches); // Remove the full match from the result
            return $matches;
        }
        return []; // Return an empty array if no match is found
    }
    
}